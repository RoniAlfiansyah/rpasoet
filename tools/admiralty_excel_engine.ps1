param(
    [Parameter(Mandatory = $true)][string]$TemplatePath,
    [Parameter(Mandatory = $true)][string]$InputPath,
    [Parameter(Mandatory = $true)][string]$OutputPath
)

$ErrorActionPreference = 'Stop'

function Convert-ToExcelSerial {
    param([datetime]$DateTimeValue)
    return [double]$DateTimeValue.ToOADate()
}

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
            $format = ''

            try {
                $format = [string] $Range.NumberFormat
            }
            catch {
                $format = ''
            }

            if ($format -match '[dmyhs]' -or $format -match 'dd' -or $format -match 'mm' -or $format -match 'yy') {
                try {
                    $dateValue = [datetime]::FromOADate($number)
                    if ([math]::Abs($number - [math]::Floor($number)) -lt 0.0000001) {
                        return $dateValue.ToString('dd/MM/yyyy')
                    }

                    return $dateValue.ToString('dd/MM/yyyy HH:mm')
                }
                catch {
                }
            }

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
        $text = ''

        try {
            $text = $Value.ToString()
        }
        catch {
            return $Default
        }

        if ([string]::IsNullOrWhiteSpace($text)) {
            return $Default
        }

        $text = $text.Trim()

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

function Write-ObservationRows {
    param(
        $Worksheet,
        [int]$StartRow,
        [object[]]$Rows
    )

    for ($index = 0; $index -lt $Rows.Count; $index++) {
        $row = $Rows[$index]
        $targetRow = $StartRow + $index

        $Worksheet.Cells.Item($targetRow, 1).Value2 = [double]($index + 1)
        $Worksheet.Cells.Item($targetRow, 2).Value2 = Convert-ToExcelSerial $row.datetime
        $Worksheet.Cells.Item($targetRow, 3).Value2 = [double]($row.water_level * 100)
        $Worksheet.Cells.Item($targetRow, 4).Value2 = [double]$row.water_level
    }
}

function Get-RowTable {
    param(
        $Worksheet,
        [int[]]$Rows,
        [string[]]$Columns,
        [string[]]$Headers
    )

    $items = @()
    foreach ($rowNumber in $Rows) {
        $item = [ordered]@{}
        for ($i = 0; $i -lt $Columns.Count; $i++) {
            $header = $Headers[$i]
            $column = $Columns[$i]
            $item[$header] = Get-CellString $Worksheet.Range($column + $rowNumber)
        }
        $items += $item
    }

    return $items
}

function Get-ForecastingRows {
    param(
        $Worksheet,
        [int[]]$Rows
    )

    $items = @()
    foreach ($rowNumber in $Rows) {
        $dateValue = $Worksheet.Range('B' + $rowNumber).Value2
        $dateLabel = ''
        $dateSerial = Convert-ToDoubleSafe $dateValue -Default -1
        if ($dateSerial -gt 0) {
            $dateLabel = ([datetime]::FromOADate($dateSerial)).ToString('dd/MM/yyyy')
        }

        $item = [ordered]@{
            no   = Get-CellString $Worksheet.Range('A' + $rowNumber)
            date = $dateLabel
            t    = Get-CellString $Worksheet.Range('E' + $rowNumber)
            m2   = Get-CellString $Worksheet.Range('F' + $rowNumber)
            s2   = Get-CellString $Worksheet.Range('G' + $rowNumber)
            n2   = Get-CellString $Worksheet.Range('H' + $rowNumber)
            k1   = Get-CellString $Worksheet.Range('I' + $rowNumber)
            o1   = Get-CellString $Worksheet.Range('J' + $rowNumber)
            m4   = Get-CellString $Worksheet.Range('K' + $rowNumber)
            ms4  = Get-CellString $Worksheet.Range('L' + $rowNumber)
            k2   = Get-CellString $Worksheet.Range('M' + $rowNumber)
            p1   = Get-CellString $Worksheet.Range('N' + $rowNumber)
            eta  = Get-CellString $Worksheet.Range('O' + $rowNumber)
        }

        $items += $item
    }

    return $items
}

$input = Get-Content -LiteralPath $InputPath -Raw | ConvertFrom-Json
$workingCopy = [System.IO.Path]::ChangeExtension($OutputPath, '.xlsx')
Copy-Item -LiteralPath $TemplatePath -Destination $workingCopy -Force

$excel = $null
$workbook = $null

try {
    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false
    $workbook = $excel.Workbooks.Open($workingCopy)

    $inputSheet = $workbook.Worksheets.Item('data asli x100')
    $skema1Sheet = $workbook.Worksheets.Item('skemA1')
    $dataAsliSheet = $workbook.Worksheets.Item('data asli')
    $skema7Sheet = $workbook.Worksheets.Item('skemA7')

    $inputSheet.Range('A8:D800').ClearContents() | Out-Null

    $rows = @($input.rows)
    if ($rows.Count -eq 0) {
        throw 'Tidak ada observasi untuk engine Excel Admiralty.'
    }

    $firstDate = [datetime]::ParseExact($rows[0].datetime, 'dd/MM/yyyy HH:mm:ss', $null)
    $skema1Sheet.Range('B5').Value2 = [math]::Floor((Convert-ToExcelSerial $firstDate))
    $dataAsliSheet.Range('B7').Value = ': ' + [string]$input.station_name
    $dataAsliSheet.Range('B8').Value = ': ' + [string]$input.station_name

    $rowCount = $rows.Count
    $preparedRows = @()
    for ($index = 0; $index -lt $rowCount; $index++) {
        $row = $rows[$index]
        $dt = [datetime]::ParseExact([string]$row.datetime, 'dd/MM/yyyy HH:mm:ss', $null)
        $level = Convert-ToDoubleSafe $row.water_level

        $preparedRows += [pscustomobject]@{
            datetime = $dt
            water_level = $level
        }
    }

    Write-ObservationRows -Worksheet $inputSheet -StartRow 8 -Rows $preparedRows

    $workbook.RefreshAll()
    $excel.CalculateFullRebuild()
    $workbook.Save()

    $componentColumns = @{
        'S0'  = 'D'
        'M2'  = 'E'
        'S2'  = 'F'
        'N2'  = 'G'
        'K1'  = 'H'
        'O1'  = 'I'
        'M4'  = 'J'
        'MS4' = 'K'
        'K2'  = 'L'
        'P1'  = 'M'
    }

    try {
        $components = @()
        foreach ($name in @('S0','M2','S2','N2','K1','O1','M4','MS4','K2','P1')) {
            $column = $componentColumns[$name]
            $amplitude = Convert-ToDoubleSafe ($skema7Sheet.Range($column + '21').Value2)
            $phaseCell = $skema7Sheet.Range($column + '22').Value2
            $phase = 0.0
            if ($null -ne $phaseCell -and $phaseCell -ne '') {
                $phase = Convert-ToDoubleSafe $phaseCell
            }

            $components += [ordered]@{
                name = $name
                amplitude = $amplitude
                phase = $phase
            }
        }
    }
    catch {
        throw "Gagal membaca komponen harmonik dari skemA7: $($_.Exception.Message)"
    }

    try {
        $skema56CosRows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA5&6') `
            -Rows @(8,9,10,11,12,13,14,15,16) `
            -Columns @('A','C','D','E','F','G','H','I','J','K') `
            -Headers @('label','base','s0','m2','s2','n2','k1','o1','m4','ms4')

        $skema2Rows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA2') `
            -Rows @(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33) `
            -Columns @('B','C','D','E','F','G','H','I','J','K','L','M','N','O') `
            -Headers @('date','x0','x1_plus','x1_minus','y1_plus','y1_minus','x2_plus','x2_minus','y2_plus','y2_minus','x4_plus','x4_minus','y4_plus','y4_minus')

        $skema1Rows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA1') `
            -Rows @(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33) `
            -Columns @('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB') `
            -Headers @('day_index','date','h00','h01','h02','h03','h04','h05','h06','h07','h08','h09','h10','h11','h12','h13','h14','h15','h16','h17','h18','h19','h20','h21','h22','h23','total','mean')

        $skema3Rows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA3') `
            -Rows @(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33) `
            -Columns @('B','C','D','E','F','G','H','I') `
            -Headers @('date','x0','x1','y1','x2','y2','x4','y4')

        $skema4Rows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA4') `
            -Rows @(6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27) `
            -Columns @('B','C','D','E','F','G') `
            -Headers @('index_code','sign','value_x','value_y','x','y')

        $skema56SinRows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA5&6') `
            -Rows @(18,19,20,21,22,23,24,25) `
            -Columns @('A','C','D','E','F','G','H','I','J','K') `
            -Headers @('label','base','s0','m2','s2','n2','k1','o1','m4','ms4')

        $skema56TotalRows = Get-RowTable -Worksheet $workbook.Worksheets.Item('skemA5&6') `
            -Rows @(26,27) `
            -Columns @('A','D','E','F','G','H','I','J','K') `
            -Headers @('label','s0','m2','s2','n2','k1','o1','m4','ms4')

        $skema7Rows = Get-RowTable -Worksheet $skema7Sheet `
            -Rows @(5,6,7,8,9,10,11,12,13,14,15,16,17,19,20,21,22) `
            -Columns @('B','D','E','F','G','H','I','J','K','L','M') `
            -Headers @('label','s0','m2','s2','n2','k1','o1','m4','ms4','k2','p1')

        $forecastRows = Get-ForecastingRows -Worksheet $workbook.Worksheets.Item('Forcasting Pasut') `
            -Rows @(18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41)
    }
    catch {
        throw "Gagal membaca tabel workbook Admiralty: $($_.Exception.Message)"
    }

    $output = [ordered]@{
        workbook_copy = $workingCopy
        components = $components
        tables = [ordered]@{
            skema1 = $skema1Rows
            skema2 = $skema2Rows
            skema3 = $skema3Rows
            skema4 = $skema4Rows
            skema56_cos = $skema56CosRows
            skema56_sin = $skema56SinRows
            skema56_total = $skema56TotalRows
            skema7 = $skema7Rows
            forecasting = $forecastRows
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
