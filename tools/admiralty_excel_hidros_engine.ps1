param(
    [Parameter(Mandatory = $true)][string]$TemplatePath,
    [Parameter(Mandatory = $true)][string]$InputPath,
    [Parameter(Mandatory = $true)][string]$OutputPath
)

$ErrorActionPreference = 'Stop'

function Get-CellString {
    param($Range)
    try {
        $value = $Range.Text
        if ($null -eq $value) {
            $value = ''
        }
        $text = $value.ToString()
        if ($text -notmatch '#') {
            return $text
        }
    }
    catch {
    }

    try {
        $value2 = $Range.Value2
        if ($null -eq $value2) {
            return ''
        }

        if ($value2 -is [byte] -or
            $value2 -is [sbyte] -or
            $value2 -is [int16] -or
            $value2 -is [uint16] -or
            $value2 -is [int32] -or
            $value2 -is [uint32] -or
            $value2 -is [int64] -or
            $value2 -is [uint64] -or
            $value2 -is [single] -or
            $value2 -is [double] -or
            $value2 -is [decimal]) {
            $number = [double] $value2
            if ([math]::Abs($number - [math]::Round($number)) -lt 0.0000001) {
                return ([math]::Round($number)).ToString([System.Globalization.CultureInfo]::InvariantCulture)
            }

            return $number.ToString('0.####', [System.Globalization.CultureInfo]::InvariantCulture)
        }

        return $value2.ToString()
    }
    catch {
        return ''
    }
}

function Convert-ToDoubleSafe {
    param($Value, [double]$Default = 0.0)

    try {
        if ($null -eq $Value) {
            return $Default
        }

        if ($Value -is [byte] -or
            $Value -is [sbyte] -or
            $Value -is [int16] -or
            $Value -is [uint16] -or
            $Value -is [int32] -or
            $Value -is [uint32] -or
            $Value -is [int64] -or
            $Value -is [uint64] -or
            $Value -is [single] -or
            $Value -is [double] -or
            $Value -is [decimal]) {
            return [double]$Value
        }

        $parsed = 0.0
        $text = $Value.ToString().Trim()

        if ([string]::IsNullOrWhiteSpace($text)) {
            return $Default
        }

        if ([double]::TryParse($text, [System.Globalization.NumberStyles]::Any, [System.Globalization.CultureInfo]::InvariantCulture, [ref]$parsed)) {
            return $parsed
        }
        if ([double]::TryParse($text, [System.Globalization.NumberStyles]::Any, [System.Globalization.CultureInfo]::CurrentCulture, [ref]$parsed)) {
            return $parsed
        }
        if ([double]::TryParse($text, [System.Globalization.NumberStyles]::Any, [System.Globalization.CultureInfo]::GetCultureInfo('id-ID'), [ref]$parsed)) {
            return $parsed
        }

        $normalized = $text -replace '\s', ''
        if ($normalized.Contains(',') -and -not $normalized.Contains('.')) {
            $normalized = $normalized.Replace(',', '.')
        }
        elseif ($normalized.Contains(',') -and $normalized.Contains('.')) {
            $normalized = $normalized.Replace('.', '').Replace(',', '.')
        }

        if ([double]::TryParse($normalized, [System.Globalization.NumberStyles]::Any, [System.Globalization.CultureInfo]::InvariantCulture, [ref]$parsed)) {
            return $parsed
        }

        return $Default
    }
    catch {
        return $Default
    }
}

function Get-MatrixRows {
    param($Worksheet)

    $items = @()
    for ($rowNumber = 9; $rowNumber -le 37; $rowNumber++) {
        $item = [ordered]@{
            tgl = Get-CellString $Worksheet.Range('A' + $rowNumber)
        }

        for ($hour = 0; $hour -lt 24; $hour++) {
            $columnLetter = [char]([int][char]'B' + $hour)
            $item['h' + ('{0:D2}' -f $hour)] = Get-CellString $Worksheet.Range(($columnLetter.ToString()) + $rowNumber)
        }

        $items += $item
    }

    return $items
}

function Get-HarmonicRows {
    param($Worksheet)

    $headers = @()
    for ($column = 28; $column -le 37; $column++) {
        $headers += (Get-CellString $Worksheet.Cells.Item(41, $column))
    }

    $items = @()
    foreach ($name in $headers) {
        if ([string]::IsNullOrWhiteSpace($name)) {
            continue
        }

        $columnIndex = 28 + [array]::IndexOf($headers, $name)
        $amplitudeValue = Convert-ToDoubleSafe $Worksheet.Cells.Item(42, $columnIndex).Value2
        $phaseValue = if ($name -eq 'S0') { 0.0 } else { Convert-ToDoubleSafe $Worksheet.Cells.Item(43, $columnIndex).Value2 }
        $items += [ordered]@{
            name = $name
            amplitude_cm = $amplitudeValue.ToString('0.####', [System.Globalization.CultureInfo]::InvariantCulture)
            phase_deg = $phaseValue.ToString('0.####', [System.Globalization.CultureInfo]::InvariantCulture)
            amplitude_display = Get-CellString $Worksheet.Cells.Item(42, $columnIndex)
            phase_display = if ($name -eq 'S0') { '0' } else { Get-CellString $Worksheet.Cells.Item(43, $columnIndex) }
        }
    }

    return $items
}

function Get-DerivationRows {
    param($Worksheet)

    $rows = @()
    foreach ($rowNumber in 45..55) {
        $item = [ordered]@{
            item = Get-CellString $Worksheet.Cells.Item($rowNumber, 26)
            note = Get-CellString $Worksheet.Cells.Item($rowNumber, 29)
        }

        if (($item.item -as [string]).Trim() -eq '' -and ($item.note -as [string]).Trim() -eq '') {
            continue
        }

        $rows += $item
    }

    return $rows
}

function Get-ClassificationRows {
    param($Worksheet)

    return @(
        [ordered]@{ item = 'Bilangan Formzahl'; value = Get-CellString $Worksheet.Range('AD61'); note = Get-CellString $Worksheet.Range('AE61') },
        [ordered]@{ item = 'M2 : 3V'; value = Get-CellString $Worksheet.Range('X61'); note = Get-CellString $Worksheet.Range('AE61') },
        [ordered]@{ item = 'N2 : 2V'; value = Get-CellString $Worksheet.Range('X62'); note = Get-CellString $Worksheet.Range('AE62') },
        [ordered]@{ item = 'Selisih (M2 - N2)'; value = Get-CellString $Worksheet.Range('X63'); note = Get-CellString $Worksheet.Range('AE63') },
        [ordered]@{ item = 'N2 : w'; value = Get-CellString $Worksheet.Range('X64'); note = '' },
        [ordered]@{ item = 'N2 : 1+W'; value = Get-CellString $Worksheet.Range('X65'); note = '' }
    )
}

function Build-DailyBuckets {
    param([object[]]$Rows)

    $buckets = [ordered]@{}
    foreach ($row in $Rows) {
        $dt = [datetime]::ParseExact([string]$row.datetime, 'dd/MM/yyyy HH:mm:ss', $null)
        $key = $dt.ToString('yyyy-MM-dd')
        if (-not $buckets.Contains($key)) {
            $buckets[$key] = [ordered]@{
                date = $dt.Date
                hours = @{}
            }
        }

        $buckets[$key].hours[$dt.Hour] = Convert-ToDoubleSafe $row.water_level
    }

    $complete = @()
    foreach ($key in $buckets.Keys) {
        if ($buckets[$key].hours.Count -eq 24) {
            $complete += $buckets[$key]
        }
    }

    return $complete | Sort-Object { $_.date }
}

$input = Get-Content -LiteralPath $InputPath -Raw | ConvertFrom-Json
$templateExtension = [System.IO.Path]::GetExtension($TemplatePath)
if ([string]::IsNullOrWhiteSpace($templateExtension)) {
    $templateExtension = '.xls'
}
$workingCopy = [System.IO.Path]::ChangeExtension($OutputPath, $templateExtension)
Copy-Item -LiteralPath $TemplatePath -Destination $workingCopy -Force

$excel = $null
$workbook = $null

try {
    $rows = @($input.rows)
    if ($rows.Count -eq 0) {
        throw 'Tidak ada observasi untuk engine Excel Admiralty Hidros.'
    }

    $dailyBuckets = @(Build-DailyBuckets -Rows $rows)
    if ($dailyBuckets.Count -lt 29) {
        throw 'Admiralty Hidros membutuhkan minimal 29 hari kalender penuh dengan 24 data per hari.'
    }

    $selectedDays = $dailyBuckets | Select-Object -First 29
    $midDate = $selectedDays[14].date

    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false
    $workbook = $excel.Workbooks.Open($workingCopy)

    $pasutSheet = $workbook.Worksheets.Item('PASUT')

    $pasutSheet.Range('A9:Y37').ClearContents() | Out-Null

    for ($dayIndex = 0; $dayIndex -lt $selectedDays.Count; $dayIndex++) {
        $rowNumber = 9 + $dayIndex
        $day = $selectedDays[$dayIndex]

        $pasutSheet.Cells.Item($rowNumber, 1).Value2 = [double]$day.date.Day
        for ($hour = 0; $hour -lt 24; $hour++) {
            $level = Convert-ToDoubleSafe $day.hours[$hour]
            $pasutSheet.Cells.Item($rowNumber, 2 + $hour).Value2 = [double]($level * 100)
        }
    }

    $pasutSheet.Range('AA5').Value2 = [double]$midDate.Day
    $pasutSheet.Range('AB5').Value2 = [double]$midDate.Month
    $pasutSheet.Range('AC5').Value2 = [double]$midDate.Year

    $excel.CalculateFullRebuild()
    $workbook.Save()

    $harmonicRows = @(Get-HarmonicRows -Worksheet $pasutSheet)
    $components = @()
    foreach ($row in $harmonicRows) {
        $components += [ordered]@{
            name = $row.name
            amplitude = Convert-ToDoubleSafe $row.amplitude_cm
            phase = Convert-ToDoubleSafe $row.phase_deg
        }
    }

    $output = [ordered]@{
        workbook_copy = $workingCopy
        components = $components
        tables = [ordered]@{
            matrix = Get-MatrixRows -Worksheet $pasutSheet
            harmonics = $harmonicRows
            derivation = Get-DerivationRows -Worksheet $pasutSheet
            classification = Get-ClassificationRows -Worksheet $pasutSheet
        }
    }

    $output | ConvertTo-Json -Depth 6 | Set-Content -LiteralPath $OutputPath -Encoding UTF8
}
finally {
    if ($workbook -ne $null) {
        $workbook.Close($true)
    }
    if ($excel -ne $null) {
        $excel.Quit()
    }
}
