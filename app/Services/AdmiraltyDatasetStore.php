<?php

namespace App\Services;

use App\Models\AdmiraltyDatasetModel;
use App\Models\AdmiraltyDatasetObservationModel;
use Config\Database;
use RuntimeException;

class AdmiraltyDatasetStore
{
    /**
     * @param array<string, mixed> $validatedResult
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public function store(array $validatedResult, string $originalFilename, string $extension, array $metadata = []): array
    {
        $summary      = $validatedResult['summary'] ?? null;
        $observations = $validatedResult['observations'] ?? null;

        if (! is_array($summary) || ! is_array($observations) || $observations === []) {
            throw new RuntimeException('Dataset validasi belum siap untuk disimpan.');
        }

        $db = Database::connect();
        $db->transStart();

        $datasetModel      = new AdmiraltyDatasetModel();
        $observationModel  = new AdmiraltyDatasetObservationModel();
        $datasetCode       = 'ADS-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $datasetId = $datasetModel->insert([
            'dataset_code'      => $datasetCode,
            'original_filename' => $originalFilename,
            'station_name'      => $this->normalizeNullableString($metadata['station_name'] ?? null),
            'latitude'          => $this->normalizeNullableDecimal($metadata['latitude'] ?? null),
            'longitude'         => $this->normalizeNullableDecimal($metadata['longitude'] ?? null),
            'timezone'          => $this->normalizeNullableString($metadata['timezone'] ?? null),
            'file_extension'    => strtolower($extension),
            'interval_minutes'  => $summary['interval_minutes'] ?? null,
            'data_count'        => $summary['valid_rows_total'] ?? count($observations),
            'start_at'          => $this->normalizeDateTime($summary['start_at'] ?? null),
            'end_at'            => $this->normalizeDateTime($summary['end_at'] ?? null),
            'gap_count'         => $summary['gap_count'] ?? 0,
            'duplicate_count'   => $summary['duplicate_rows_total'] ?? 0,
            'invalid_row_count' => $summary['invalid_rows_total'] ?? 0,
            'validation_status' => (string) ($summary['quality_status'] ?? 'validated'),
        ], true);

        if (! is_numeric($datasetId)) {
            throw new RuntimeException('Header dataset gagal disimpan.');
        }

        $rows = [];
        foreach ($observations as $row) {
            $rows[] = [
                'dataset_id'  => (int) $datasetId,
                'row_number'  => (int) ($row['line'] ?? 0),
                'observed_at' => (string) ($row['datetime'] ?? ''),
                'water_level' => number_format((float) ($row['water_level'] ?? 0), 4, '.', ''),
            ];
        }

        $observationModel->insertBatch($rows, null, 200);

        $db->transComplete();

        if (! $db->transStatus()) {
            throw new RuntimeException('Observasi dataset gagal disimpan ke database.');
        }

        return [
            'dataset_id'   => (int) $datasetId,
            'dataset_code' => $datasetCode,
            'data_count'   => count($rows),
            'station_name' => $this->normalizeNullableString($metadata['station_name'] ?? null),
            'latitude'     => $this->normalizeNullableDecimal($metadata['latitude'] ?? null),
            'longitude'    => $this->normalizeNullableDecimal($metadata['longitude'] ?? null),
            'timezone'     => $this->normalizeNullableString($metadata['timezone'] ?? null),
            'validation_status' => (string) ($summary['quality_status'] ?? 'validated'),
            'validation_label' => (string) ($summary['quality_label'] ?? 'Belum diklasifikasikan'),
        ];
    }

    private function normalizeDateTime($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $value);

        return $parsed instanceof \DateTimeImmutable ? $parsed->format('Y-m-d H:i:s') : null;
    }

    private function normalizeNullableString($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeNullableDecimal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace(',', '.', (string) $value);

        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 6, '.', '');
    }
}
