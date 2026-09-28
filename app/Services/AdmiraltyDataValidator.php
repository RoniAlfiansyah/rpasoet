<?php

namespace App\Services;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class AdmiraltyDataValidator
{
    private const MIN_DATA_POINTS = 360;
    private const MAX_DATA_POINTS = 720;
    private const MAX_WARNING_GAPS = 2;
    private const MAX_WARNING_MISSING_POINTS = 6;
    private const MAX_WARNING_DUPLICATES = 3;
    private const MAX_WARNING_INVALID_ROWS = 10;

    /**
     * @return array<string, mixed>
     */
    public function validateFile(string $path, string $extension): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('File data tidak ditemukan.');
        }

        $rows          = [];
        $invalidRows   = [];
        $timezone      = new DateTimeZone('Asia/Jakarta');
        $lineNumber    = 0;

        foreach ($this->readSourceRows($path, $extension) as $columns) {
            $lineNumber++;
            $parsed = $this->parseRow($columns, $timezone);

            if ($parsed === null) {
                continue;
            }

            if ($parsed['valid'] === false) {
                $invalidRows[] = [
                    'line'   => $lineNumber,
                    'reason' => $parsed['reason'],
                    'raw'    => implode(' | ', $columns),
                ];

                continue;
            }

            $rows[] = [
                'line'        => $lineNumber,
                'datetime'    => $parsed['datetime'],
                'datetime_db' => $parsed['datetime']->format('Y-m-d H:i:s'),
                'water_level' => $parsed['water_level'],
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('Tidak ada baris data valid yang bisa dibaca dari file.');
        }

        usort($rows, static fn (array $a, array $b): int => $a['datetime'] <=> $b['datetime']);

        $duplicates       = [];
        $uniqueRows       = [];
        $seenTimestamps   = [];

        foreach ($rows as $row) {
            $key = $row['datetime_db'];

            if (isset($seenTimestamps[$key])) {
                $duplicates[] = $row;
                continue;
            }

            $seenTimestamps[$key] = true;
            $uniqueRows[]         = $row;
        }

        $intervals       = $this->buildIntervals($uniqueRows);
        $dominantMinutes = $this->detectDominantIntervalMinutes($intervals);
        $gapSummary      = $this->buildGapSummary($uniqueRows, $dominantMinutes);
        $dataCount       = count($uniqueRows);
        $startDate       = $uniqueRows[0]['datetime'];
        $endDate         = $uniqueRows[array_key_last($uniqueRows)]['datetime'];
        $quality         = $this->assessDatasetQuality($dominantMinutes, $gapSummary, $dataCount, count($duplicates), count($invalidRows));

        return [
            'summary' => [
                'rows_total'            => count($rows),
                'valid_rows_total'      => $dataCount,
                'invalid_rows_total'    => count($invalidRows),
                'duplicate_rows_total'  => count($duplicates),
                'interval_minutes'      => $dominantMinutes,
                'interval_label'        => $dominantMinutes === null ? 'Tidak dapat ditentukan' : $dominantMinutes . ' menit',
                'start_at'              => $startDate->format('d/m/Y H:i:s'),
                'end_at'                => $endDate->format('d/m/Y H:i:s'),
                'has_gap'               => $gapSummary['has_gap'],
                'gap_count'             => $gapSummary['gap_count'],
                'is_minimum_satisfied'  => $dataCount >= self::MIN_DATA_POINTS,
                'is_maximum_satisfied'  => $dataCount <= self::MAX_DATA_POINTS,
                'minimum_required'      => self::MIN_DATA_POINTS,
                'maximum_allowed'       => self::MAX_DATA_POINTS,
                'quality_status'        => $quality['status'],
                'quality_label'         => $quality['label'],
                'quality_note'          => $quality['note'],
                'quality_can_analyze'   => $quality['can_analyze'],
                'quality_reasons'       => $quality['reasons'],
            ],
            'intervals' => [
                'dominant_minutes' => $dominantMinutes,
                'distribution'     => $intervals,
            ],
            'gaps' => $gapSummary,
            'preview' => array_map(
                static fn (array $row): array => [
                    'line'        => $row['line'],
                    'datetime'    => $row['datetime']->format('d/m/Y H:i:s'),
                    'water_level' => number_format($row['water_level'], 4, '.', ''),
                ],
                array_slice($uniqueRows, 0, 20),
            ),
            'observations' => array_map(
                static fn (array $row): array => [
                    'line'        => $row['line'],
                    'datetime'    => $row['datetime']->format('Y-m-d H:i:s'),
                    'water_level' => (float) $row['water_level'],
                ],
                $uniqueRows,
            ),
            'invalid_rows' => array_slice($invalidRows, 0, 20),
            'duplicate_rows' => array_map(
                static fn (array $row): array => [
                    'line'     => $row['line'],
                    'datetime' => $row['datetime']->format('d/m/Y H:i:s'),
                ],
                array_slice($duplicates, 0, 20),
            ),
        ];
    }

    /**
     * @param array{has_gap: bool, gap_count: int, gap_segments?: int, total_missing_points?: int} $gapSummary
     * @return array{status: string, label: string, note: string, can_analyze: bool, reasons: list<string>}
     */
    private function assessDatasetQuality(?int $dominantMinutes, array $gapSummary, int $dataCount, int $duplicateCount, int $invalidRowCount): array
    {
        $reasons = [];

        if ($dominantMinutes !== 60) {
            $reasons[] = 'Interval dominan belum 60 menit.';
        }

        if ($dataCount < self::MIN_DATA_POINTS) {
            $reasons[] = 'Jumlah data valid masih di bawah minimum 360 titik.';
        }

        if ($dataCount > self::MAX_DATA_POINTS) {
            $reasons[] = 'Jumlah data valid melebihi maksimum 720 titik.';
        }

        if ($duplicateCount > 0) {
            $reasons[] = 'Masih ada duplikasi timestamp sebanyak ' . $duplicateCount . ' baris.';
        }

        if ($invalidRowCount > 0) {
            $reasons[] = 'Masih ada baris invalid sebanyak ' . $invalidRowCount . ' baris.';
        }

        if (($gapSummary['gap_segments'] ?? 0) > 0) {
            $reasons[] = 'Masih ada gap data sebanyak ' . (int) ($gapSummary['gap_segments'] ?? 0) . ' segmen dengan ' . (int) ($gapSummary['total_missing_points'] ?? 0) . ' titik hilang.';
        }

        $isStrictlyReady =
            $dominantMinutes === 60
            && $dataCount >= self::MIN_DATA_POINTS
            && $dataCount <= self::MAX_DATA_POINTS
            && ! ($gapSummary['has_gap'] ?? false)
            && $duplicateCount === 0
            && $invalidRowCount === 0;

        if ($isStrictlyReady) {
            return [
                'status' => 'analysis_ready',
                'label' => 'Layak Analisa',
                'note' => 'Dataset memenuhi syarat utama untuk analisa harmonik dan prediksi.',
                'can_analyze' => true,
                'reasons' => [],
            ];
        }

        $isWarningEligible =
            $dominantMinutes === 60
            && $dataCount >= self::MIN_DATA_POINTS
            && $dataCount <= self::MAX_DATA_POINTS
            && (int) ($gapSummary['gap_segments'] ?? 0) <= self::MAX_WARNING_GAPS
            && (int) ($gapSummary['total_missing_points'] ?? 0) <= self::MAX_WARNING_MISSING_POINTS
            && $duplicateCount <= self::MAX_WARNING_DUPLICATES
            && $invalidRowCount <= self::MAX_WARNING_INVALID_ROWS;

        if ($isWarningEligible) {
            return [
                'status' => 'analysis_warning',
                'label' => 'Layak Dengan Peringatan',
                'note' => 'Dataset masih bisa dianalisa, tetapi hasil perlu dibaca dengan kehati-hatian.',
                'can_analyze' => true,
                'reasons' => $reasons,
            ];
        }

        return [
            'status' => 'analysis_blocked',
            'label' => 'Tidak Layak Analisa',
            'note' => 'Dataset perlu dibersihkan atau dilengkapi sebelum masuk tahap analisa.',
            'can_analyze' => false,
            'reasons' => $reasons,
        ];
    }

    /**
     * @return iterable<int, list<string|null>>
     */
    private function readSourceRows(string $path, string $extension): iterable
    {
        return match (strtolower($extension)) {
            'csv', 'txt' => $this->readCsvRows($path),
            'xlsx'       => $this->readXlsxRows($path),
            default      => throw new RuntimeException('Format file belum didukung. Gunakan CSV, TXT, atau XLSX.'),
        };
    }

    /**
     * @return iterable<int, list<string|null>>
     */
    private function readCsvRows(string $path): iterable
    {
        $delimiter = $this->detectDelimiter($path);
        $handle    = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('File CSV tidak bisa dibaca.');
        }

        try {
            while (($columns = fgetcsv($handle, 0, $delimiter)) !== false) {
                yield $columns;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return iterable<int, list<string|null>>
     */
    private function readXlsxRows(string $path): iterable
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP PHP belum aktif, sehingga file XLSX belum bisa dibaca.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak bisa dibuka.');
        }

        try {
            $sharedStrings = $this->loadSharedStrings($zip);
            $worksheetPath = $this->resolveFirstWorksheetPath($zip);
            $worksheetXml  = $zip->getFromName($worksheetPath);

            if (! is_string($worksheetXml) || $worksheetXml === '') {
                throw new RuntimeException('Worksheet pertama pada file XLSX tidak ditemukan.');
            }

            $worksheet = simplexml_load_string($worksheetXml);
            if (! $worksheet instanceof SimpleXMLElement || ! isset($worksheet->sheetData->row)) {
                throw new RuntimeException('Isi worksheet XLSX tidak bisa diparsing.');
            }

            foreach ($worksheet->sheetData->row as $row) {
                $values = [];

                foreach ($row->c as $cell) {
                    $reference   = (string) ($cell['r'] ?? '');
                    $columnIndex = $this->columnReferenceToIndex($reference);
                    $type        = (string) ($cell['t'] ?? '');
                    $value       = isset($cell->v) ? (string) $cell->v : '';

                    if ($type === 's') {
                        $sharedIndex = (int) $value;
                        $value = $sharedStrings[$sharedIndex] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = isset($cell->is->t) ? (string) $cell->is->t : '';
                    }

                    $values[$columnIndex] = $value;
                }

                if ($values === []) {
                    yield [];
                    continue;
                }

                ksort($values);
                $dense = [];
                $max   = max(array_keys($values));

                for ($index = 0; $index <= $max; $index++) {
                    $dense[] = $values[$index] ?? '';
                }

                yield $dense;
            }
        } finally {
            $zip->close();
        }
    }

    private function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ',';
        }

        $firstLine = (string) fgets($handle);
        fclose($handle);

        $scores    = [
            ','  => substr_count($firstLine, ','),
            ';'  => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($scores);
        $delimiter = array_key_first($scores);

        return is_string($delimiter) ? $delimiter : ',';
    }

    /**
     * @param list<string|null> $columns
     * @return array<string, mixed>|null
     */
    private function parseRow(array $columns, DateTimeZone $timezone): ?array
    {
        $trimmed = array_values(array_map(static fn ($value): string => trim((string) $value), $columns));
        $filled  = array_values(array_filter($trimmed, static fn (string $value): bool => $value !== ''));

        if ($filled === []) {
            return null;
        }

        if ($this->isHeaderRow($filled, $timezone)) {
            return null;
        }

        $datetimeIndex = null;
        $datetime      = null;

        foreach ($trimmed as $index => $value) {
            if ($value === '') {
                continue;
            }

            $parsedDate = $this->parseDateTime($value, $timezone);
            if ($parsedDate instanceof DateTimeImmutable) {
                $datetimeIndex = $index;
                $datetime      = $parsedDate;
                break;
            }
        }

        if (! $datetime instanceof DateTimeImmutable) {
            return [
                'valid'  => false,
                'reason' => 'Kolom tanggal/jam tidak ditemukan atau format tidak dikenali.',
            ];
        }

        $waterLevel = null;
        for ($index = count($trimmed) - 1; $index >= 0; $index--) {
            if ($index === $datetimeIndex) {
                continue;
            }

            $candidate = str_replace(',', '.', $trimmed[$index]);
            if ($candidate === '' || ! is_numeric($candidate)) {
                continue;
            }

            $waterLevel = (float) $candidate;
            break;
        }

        if (! is_float($waterLevel)) {
            return [
                'valid'  => false,
                'reason' => 'Kolom tinggi muka air tidak ditemukan.',
            ];
        }

        return [
            'valid'       => true,
            'datetime'    => $datetime,
            'water_level' => $waterLevel,
        ];
    }

    /**
     * @param list<string> $filled
     */
    private function isHeaderRow(array $filled, DateTimeZone $timezone): bool
    {
        if ($filled === []) {
            return false;
        }

        $normalized = array_map(static function (string $value): string {
            $value = strtolower(trim($value));
            $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

            return trim($value);
        }, $filled);

        $joined = ' ' . implode(' ', $normalized) . ' ';
        $headerKeywords = [
            ' timestamp ',
            ' datetime ',
            ' date ',
            ' time ',
            ' tide level ',
            ' water level ',
            ' elevasi ',
            ' tinggi muka air ',
            ' level ',
        ];

        $matchedKeywordCount = 0;
        foreach ($headerKeywords as $keyword) {
            if (str_contains($joined, $keyword)) {
                $matchedKeywordCount++;
            }
        }

        if ($matchedKeywordCount < 2) {
            return false;
        }

        foreach ($filled as $value) {
            $candidate = str_replace(',', '.', trim($value));
            if ($this->parseDateTime($value, $timezone) instanceof DateTimeImmutable) {
                return false;
            }
            if ($candidate !== '' && is_numeric($candidate)) {
                return false;
            }
        }

        return true;
    }

    private function parseDateTime(string $value, DateTimeZone $timezone): ?DateTimeImmutable
    {
        if (is_numeric($value)) {
            $excelDate = $this->parseExcelSerialDate((float) $value, $timezone);
            if ($excelDate instanceof DateTimeImmutable) {
                return $excelDate;
            }
        }

        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'd/m/Y H.i.s',
            'd/m/Y H.i',
        ];

        foreach ($formats as $format) {
            $parsed = DateTimeImmutable::createFromFormat($format, $value, $timezone);
            if ($parsed instanceof DateTimeImmutable) {
                return $parsed;
            }
        }

        return null;
    }

    private function parseExcelSerialDate(float $value, DateTimeZone $timezone): ?DateTimeImmutable
    {
        if ($value < 20000 || $value > 80000) {
            return null;
        }

        $seconds = (int) round(($value - 25569) * 86400);

        return (new DateTimeImmutable('@' . $seconds))->setTimezone($timezone);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<int, int>
     */
    private function buildIntervals(array $rows): array
    {
        $distribution = [];

        for ($index = 1, $count = count($rows); $index < $count; $index++) {
            $minutes = (int) round(($rows[$index]['datetime']->getTimestamp() - $rows[$index - 1]['datetime']->getTimestamp()) / 60);
            if ($minutes <= 0) {
                continue;
            }

            $distribution[$minutes] = ($distribution[$minutes] ?? 0) + 1;
        }

        ksort($distribution);

        return $distribution;
    }

    /**
     * @param array<int, int> $distribution
     */
    private function detectDominantIntervalMinutes(array $distribution): ?int
    {
        if ($distribution === []) {
            return null;
        }

        arsort($distribution);

        $minutes = array_key_first($distribution);

        return is_int($minutes) ? $minutes : null;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<string, mixed>
     */
    private function buildGapSummary(array $rows, ?int $intervalMinutes): array
    {
        if ($intervalMinutes === null || $intervalMinutes <= 0 || count($rows) < 2) {
            return [
                'has_gap'      => false,
                'gap_count'    => 0,
                'gap_segments' => 0,
                'total_missing_points' => 0,
                'gap_examples' => [],
            ];
        }

        $gapExamples = [];
        $gapCount    = 0;
        $gapSegments = 0;

        for ($index = 1, $count = count($rows); $index < $count; $index++) {
            $previous = $rows[$index - 1]['datetime'];
            $current  = $rows[$index]['datetime'];
            $minutes  = (int) round(($current->getTimestamp() - $previous->getTimestamp()) / 60);

            if ($minutes <= $intervalMinutes) {
                continue;
            }

            $missing = (int) floor($minutes / $intervalMinutes) - 1;
            if ($missing <= 0) {
                continue;
            }

            $gapSegments++;
            $gapCount += $missing;

            if (count($gapExamples) < 10) {
                $gapExamples[] = [
                    'from'            => $previous->format('d/m/Y H:i:s'),
                    'to'              => $current->format('d/m/Y H:i:s'),
                    'missing_points'  => $missing,
                    'distance_minutes'=> $minutes,
                ];
            }
        }

        return [
            'has_gap'      => $gapCount > 0,
            'gap_count'    => $gapCount,
            'gap_segments' => $gapSegments,
            'total_missing_points' => $gapCount,
            'gap_examples' => $gapExamples,
        ];
    }

    /**
     * @return list<string>
     */
    private function loadSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if (! is_string($xml) || $xml === '') {
            return [];
        }

        $document = simplexml_load_string($xml);
        if (! $document instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];
        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $parts = [];
            foreach ($item->r as $run) {
                $parts[] = (string) ($run->t ?? '');
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function resolveFirstWorksheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml     = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! is_string($workbookXml) || ! is_string($relsXml) || $workbookXml === '' || $relsXml === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels     = simplexml_load_string($relsXml);

        if (! $workbook instanceof SimpleXMLElement || ! $rels instanceof SimpleXMLElement) {
            return 'xl/worksheets/sheet1.xml';
        }

        $namespaces = $workbook->getNamespaces(true);
        $relationshipNamespace = $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $sheetAttributes = $workbook->sheets->sheet[0]->attributes($relationshipNamespace);
        $relationshipId  = (string) ($sheetAttributes['id'] ?? '');

        if ($relationshipId === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        foreach ($rels->Relationship as $relationship) {
            if ((string) ($relationship['Id'] ?? '') !== $relationshipId) {
                continue;
            }

            $target = (string) ($relationship['Target'] ?? '');
            if ($target === '') {
                break;
            }

            return str_starts_with($target, 'xl/') ? $target : 'xl/' . ltrim($target, '/');
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function columnReferenceToIndex(string $reference): int
    {
        if (! preg_match('/^[A-Z]+/i', $reference, $matches)) {
            return 0;
        }

        $letters = strtoupper($matches[0]);
        $index   = 0;

        for ($position = 0, $length = strlen($letters); $position < $length; $position++) {
            $index = ($index * 26) + (ord($letters[$position]) - 64);
        }

        return max(0, $index - 1);
    }
}
