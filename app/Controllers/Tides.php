<?php

namespace App\Controllers;

use App\Services\AdmiraltyCalculator;
use App\Services\AdmiraltyDataValidator;
use App\Services\AdmiraltyAnalysisRunStore;
use App\Services\AdmiraltyDatasetStore;
use App\Services\TideFetcher;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tides as TidesConfig;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

class Tides extends BaseController
{
    public function index(): string
    {
        $config = config(TidesConfig::class);
        $stationOptions = $this->loadStationOptions();

        return view('tides/index', [
            'title'          => 'Data Pasang Surut SRGI',
            'stations'       => $stationOptions,
            'stationsSource' => count($stationOptions) > count($config->getStations()) ? 'srgi' : 'config',
            'today'          => (new DateTimeImmutable('now', new DateTimeZone($config->sourceTimezone)))->format('Y-m-d'),
            'defaultFrom'    => (new DateTimeImmutable('now', new DateTimeZone($config->sourceTimezone)))->sub(new DateInterval('P6D'))->format('Y-m-d'),
            'maxRangeDays'   => 31,
            'resolutions'    => [5, 10, 15, 30, 60],
            'defaultResolution' => 10,
        ]);
    }

    public function admiralty(): string
    {
        return view('admiralty/index', [
            'title' => 'Admiralty',
            'initialValidatedResult' => session('admiralty_validated_result'),
            'initialDatasetMeta'     => session('admiralty_dataset_meta'),
        ]);
    }

    public function resetAdmiralty(): ResponseInterface
    {
        session()->remove('admiralty_validated_result');
        session()->remove('admiralty_upload_meta');
        session()->remove('admiralty_dataset_meta');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data validasi dan sesi berhasil direset.',
        ]);
    }

    public function validateAdmiraltyUpload(): ResponseInterface
    {
        $file = $this->request->getFile('tide_file');

        if ($file === null || ! $file->isValid()) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'File data wajib diunggah.',
            ]);
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['csv', 'txt', 'xlsx'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'File harus berformat CSV, TXT, atau XLSX.',
            ]);
        }

        try {
            $result = (new AdmiraltyDataValidator())->validateFile($file->getTempName(), $extension);
            session()->set('admiralty_validated_result', $result);
            session()->set('admiralty_upload_meta', [
                'original_filename' => $file->getClientName(),
                'extension'         => $extension,
            ]);
            session()->remove('admiralty_dataset_meta');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Validasi data pasang surut selesai.',
                'result'  => $result,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function saveAdmiraltyDataset(): ResponseInterface
    {
        $validatedResult = session('admiralty_validated_result');
        $uploadMeta      = session('admiralty_upload_meta');
        $metadata        = [
            'station_name' => trim((string) $this->request->getPost('station_name')),
            'latitude'     => trim((string) $this->request->getPost('latitude')),
            'longitude'    => trim((string) $this->request->getPost('longitude')),
            'timezone'     => trim((string) $this->request->getPost('timezone')),
        ];

        if (! is_array($validatedResult) || ! is_array($uploadMeta)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Dataset validasi belum tersedia. Silakan upload dan validasi ulang.',
            ]);
        }

        $summary = is_array($validatedResult['summary'] ?? null) ? $validatedResult['summary'] : [];
        if (($summary['quality_can_analyze'] ?? false) !== true) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Dataset berstatus Tidak Layak Analisa dan belum bisa disimpan ke tahap perhitungan.',
            ]);
        }

        if ($metadata['station_name'] === '' || $metadata['latitude'] === '' || $metadata['longitude'] === '' || $metadata['timezone'] === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Nama stasiun, latitude, longitude, dan time zone wajib diisi.',
            ]);
        }

        try {
            $result = (new AdmiraltyDatasetStore())->store(
                $validatedResult,
                (string) ($uploadMeta['original_filename'] ?? 'dataset'),
                (string) ($uploadMeta['extension'] ?? 'csv'),
                $metadata,
            );

            session()->set('admiralty_dataset_meta', $result);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Dataset berhasil disimpan.',
                'result'  => $result,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function startAdmiraltyCalculation(): ResponseInterface
    {
        $datasetMeta     = session('admiralty_dataset_meta');
        $modelNames      = $this->request->getPost('model_names');
        if (! is_array($datasetMeta)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Dataset belum disimpan. Simpan dataset terlebih dahulu sebelum menghitung model.',
            ]);
        }

        $validationStatus = (string) ($datasetMeta['validation_status'] ?? '');
        if ($validationStatus === 'analysis_blocked') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Dataset saat ini berstatus Tidak Layak Analisa. Perbaiki kualitas data terlebih dahulu sebelum menghitung model.',
            ]);
        }

        if (! is_array($modelNames)) {
            $modelNames = array_filter([trim((string) $modelNames)]);
        }

        $allowedModels = ['admiralty_indonesia', 'admiralty_hidros', 'admiralty_cat_a', 'least_square'];
        $modelNames    = array_values(array_filter(
            array_map(static fn ($value): string => trim((string) $value), $modelNames),
            static fn (string $value): bool => in_array($value, $allowedModels, true),
        ));

        if ($modelNames === []) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Pilih minimal satu model perhitungan.',
            ]);
        }

        try {
            $calculator = new AdmiraltyCalculator();
            $runStore   = new AdmiraltyAnalysisRunStore();
            $results    = [];

            foreach ($modelNames as $modelName) {
                $result = $calculator->prepareCalculationFromDataset(
                    (int) ($datasetMeta['dataset_id'] ?? 0),
                    $modelName,
                );
                $result['dataset_meta'] = $datasetMeta;
                $result['run_meta']     = $runStore->store(
                    (int) ($datasetMeta['dataset_id'] ?? 0),
                    $modelName,
                    $result,
                );
                $results[] = $result;
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tahap persiapan perhitungan Admiralty berhasil dibuat untuk ' . count($results) . ' model.',
                'results' => $results,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function generateAdmiraltyPrediction(): ResponseInterface
    {
        $runId = (int) $this->request->getPost('run_id');
        $startAt = trim((string) $this->request->getPost('start_at'));
        $endAt = trim((string) $this->request->getPost('end_at'));
        $intervalMinutes = (int) $this->request->getPost('interval_minutes');
        $predictionAdjustment = [
            'amplitude_percent' => $this->request->getPost('adjustment_amplitude_percent'),
            'phase_degrees' => $this->request->getPost('adjustment_phase_degrees'),
            'p1_amplitude_percent' => $this->request->getPost('adjustment_p1_amplitude_percent'),
            'p1_phase_degrees' => $this->request->getPost('adjustment_p1_phase_degrees'),
            'time_shift_hours' => $this->request->getPost('adjustment_time_shift_hours'),
        ];
        if ($runId <= 0 || $startAt === '' || $endAt === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Run, waktu mulai, dan waktu akhir prediksi wajib diisi.',
            ]);
        }

        try {
            $result = (new AdmiraltyCalculator())->generatePredictionFromRun(
                $runId,
                $startAt,
                $endAt,
                $intervalMinutes,
                'admiralty_only',
                ['prediction_adjustment' => $predictionAdjustment],
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Prediksi pasang surut berhasil dibuat.',
                'result'  => $result,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function downloadSample(string $type = '30days'): ResponseInterface
    {
        $filename = ($type === '15days' || $type === '15hari')
            ? 'sample_pasut_15hari.csv'
            : 'sample_pasut_30hari.csv';

        $candidates = [
            FCPATH . 'public/samples/' . $filename,
            ROOTPATH . 'public/samples/' . $filename,
            ROOTPATH . $filename,
            FCPATH . $filename,
        ];

        $filePath = null;
        foreach ($candidates as $cand) {
            if (is_file($cand)) {
                $filePath = $cand;
                break;
            }
        }

        if ($filePath === null) {
            return $this->response->setStatusCode(404)->setBody('Sample data tidak ditemukan di server.');
        }

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function fetchRange(): ResponseInterface
    {
        $stationInput = $this->request->getGet('station');
        $dateFrom     = trim((string) $this->request->getGet('date_from'));
        $dateTo       = trim((string) $this->request->getGet('date_to'));
        try {
            $resolution = $this->resolveResolution($this->request->getGet('resolution'));
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $config = config(TidesConfig::class);

        try {
            $stations = $this->resolveStations($stationInput, $this->loadStationOptions());
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        if ($dateFrom === '' || $dateTo === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Tanggal mulai dan tanggal selesai wajib diisi.',
            ]);
        }

        try {
            [$startDate, $endDate] = $this->normalizeDateRange($dateFrom, $dateTo, $config->sourceTimezone);
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $fetcher = service('tideFetcher');
        $tasks   = $this->buildTasks($stations, $startDate, $endDate);
        $rows    = [];
        $logs    = [];

        foreach ($tasks as $task) {
            try {
                $result = $fetcher->fetchStationRows($task['station'], $task['date'], $resolution);
                $completeness = $this->assessCompleteness($result['rows'], $task['date'], $resolution);
                $filledRows = $this->fillMissingRows(
                    $task['station'],
                    $task['date'],
                    $resolution,
                    $result['rows'],
                );
                $logs[] = [
                    'station' => $task['station'],
                    'date'    => $task['date'],
                    'status'  => 'success',
                    'message' => count($result['rows']) . ' data diterima, ' . $completeness['missing_points'] . ' timestamp diisi 0',
                    'completeness' => $completeness,
                ];

                foreach ($filledRows as $row) {
                    $rows[$row['station_code'] . '|' . $row['measured_at_utc']] = $row;
                }
            } catch (Throwable $e) {
                $logs[] = [
                    'station' => $task['station'],
                    'date'    => $task['date'],
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $rows = array_values($rows);
        usort($rows, static function (array $left, array $right): int {
            return strcmp($left['measured_at_utc'], $right['measured_at_utc']);
        });

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Fetch selesai.',
            'meta'    => [
                'stations'    => $stations,
                'date_from'   => $startDate->format('Y-m-d'),
                'date_to'     => $endDate->format('Y-m-d'),
                'days'        => $startDate->diff($endDate)->days + 1,
                'tasks_total' => count($tasks),
                'rows_total'  => count($rows),
                'resolution'  => $resolution,
            ],
            'rows'     => $rows,
            'logs'     => $logs,
        ]);
    }

    public function fetchDay(): ResponseInterface
    {
        $station = trim((string) $this->request->getGet('station'));
        $date    = trim((string) $this->request->getGet('date'));
        try {
            $resolution = $this->resolveResolution($this->request->getGet('resolution'));
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        if ($station === '' || $date === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Parameter station dan date wajib diisi.',
            ]);
        }

        try {
            $result = service('tideFetcher')->fetchStationRows($station, $date, $resolution);
            $completeness = $this->assessCompleteness(
                $result['rows'],
                (string) $result['date'],
                $result['resolution_minutes'],
            );
            $filledRows = $this->fillMissingRows(
                $result['station_code'],
                (string) $result['date'],
                $result['resolution_minutes'],
                $result['rows'],
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => count($result['rows']) . ' data diterima, ' . $completeness['missing_points'] . ' timestamp diisi 0.',
                'station' => $result['station_code'],
                'date'    => $result['date'],
                'resolution' => $result['resolution_minutes'],
                'completeness' => $completeness,
                'rows'    => $filledRows,
            ]);
        } catch (Throwable $e) {
            log_message(
                'error',
                'Web tide fetch failed for station {station} on {date}: {message}',
                [
                    'station' => strtoupper($station),
                    'date'    => $date,
                    'message' => $e->getMessage(),
                ],
            );

            return $this->response->setStatusCode(502)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
                'station' => strtoupper($station),
                'date'    => $date,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function resolveStations(?string $stationInput, array $stationOptions): array
    {
        $allStations = array_values(array_map(
            static fn (array $station): string => strtoupper((string) $station['code']),
            $stationOptions,
        ));

        if (! is_string($stationInput) || trim($stationInput) === '') {
            return $allStations;
        }

        $station = strtoupper(trim($stationInput));
        if (! in_array($station, $allStations, true)) {
            throw new RuntimeException('Stasiun yang dipilih tidak terdaftar di konfigurasi.');
        }

        return [$station];
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    private function loadStationOptions(): array
    {
        try {
            return service('tideFetcher')->getAvailableStations();
        } catch (Throwable $e) {
            log_message('warning', 'Falling back to configured station list: {message}', [
                'message' => $e->getMessage(),
            ]);

            $config = config(TidesConfig::class);

            return array_map(
                static fn (string $code): array => [
                    'code'  => $code,
                    'label' => $code,
                ],
                $config->getStations(),
            );
        }
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function normalizeDateRange(string $dateFrom, string $dateTo, string $timezone): array
    {
        $tz    = new DateTimeZone($timezone);
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom, $tz);
        $end   = DateTimeImmutable::createFromFormat('Y-m-d', $dateTo, $tz);

        if (! $start instanceof DateTimeImmutable || ! $end instanceof DateTimeImmutable) {
            throw new RuntimeException('Format tanggal harus YYYY-MM-DD.');
        }

        if ($start > $end) {
            throw new RuntimeException('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
        }

        $days = $start->diff($end)->days + 1;
        if ($days > 31) {
            throw new RuntimeException('Rentang tanggal maksimal 31 hari untuk sekali fetch.');
        }

        return [$start, $end];
    }

    /**
     * @param list<string> $stations
     * @return list<array{station: string, date: string}>
     */
    private function buildTasks(array $stations, DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $tasks  = [];
        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate->add(new DateInterval('P1D')));

        foreach ($stations as $station) {
            foreach ($period as $date) {
                $tasks[] = [
                    'station' => $station,
                    'date'    => $date->format('Y-m-d'),
                ];
            }
        }

        return $tasks;
    }

    private function resolveResolution($resolutionInput): int
    {
        $resolution = (int) ($resolutionInput ?? 10);
        $allowed    = [5, 10, 15, 30, 60];

        if (! in_array($resolution, $allowed, true)) {
            throw new RuntimeException('Resolusi harus salah satu dari 5, 10, 15, 30, atau 60 menit.');
        }

        return $resolution;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{status: string, expected_points: int, actual_points: int, missing_points: int}
     */
    private function assessCompleteness(array $rows, string $requestedDate, int $resolution): array
    {
        $expectedPoints = (int) (1440 / $resolution);
        $validPoints = [];

        foreach ($rows as $row) {
            $measuredAtUtc = $row['measured_at_utc'] ?? null;
            if (! is_string($measuredAtUtc) || trim($measuredAtUtc) === '') {
                continue;
            }

            if (! $this->isMeasurementOnRequestedUtcDate($measuredAtUtc, $requestedDate)) {
                continue;
            }

            $validPoints[$measuredAtUtc] = true;
        }

        $actualPoints  = count($validPoints);
        $missingPoints = max(0, $expectedPoints - $actualPoints);

        return [
            'status'          => $missingPoints === 0 ? 'complete' : 'gap',
            'expected_points' => $expectedPoints,
            'actual_points'   => $actualPoints,
            'missing_points'  => $missingPoints,
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function fillMissingRows(string $stationCode, string $requestedDate, int $resolution, array $rows): array
    {
        $utcTz    = new DateTimeZone('UTC');
        $indexed  = [];

        foreach ($rows as $row) {
            if (! isset($row['measured_at_utc']) || ! is_string($row['measured_at_utc'])) {
                continue;
            }

            if (! $this->isMeasurementOnRequestedUtcDate($row['measured_at_utc'], $requestedDate)) {
                continue;
            }

            $row['is_gap_fill'] = false;
            $indexed[$row['measured_at_utc']] = $row;
        }

        $start  = new DateTimeImmutable($requestedDate . ' 00:00:00', $utcTz);
        $end    = $start->add(new DateInterval('P1D'));
        $step   = new DateInterval('PT' . $resolution . 'M');
        $period = new DatePeriod($start, $step, $end);

        foreach ($period as $dateTime) {
            $measuredAtUtc = $dateTime->setTimezone($utcTz)->format('Y-m-d H:i:s');

            if (isset($indexed[$measuredAtUtc])) {
                continue;
            }

            $indexed[$measuredAtUtc] = [
                'station_code'    => $stationCode,
                'measured_at_utc' => $measuredAtUtc,
                'prs1'            => null,
                'enc1'            => null,
                'rad1'            => null,
                'water_level'     => '0.000',
                'water_level_source' => 'gap_fill',
                'is_gap_fill'     => true,
            ];
        }

        $filledRows = array_values($indexed);
        usort($filledRows, static function (array $left, array $right): int {
            return strcmp($left['measured_at_utc'], $right['measured_at_utc']);
        });

        return $filledRows;
    }

    private function isMeasurementOnRequestedUtcDate(
        string $measuredAtUtc,
        string $requestedDate,
    ): bool {
        $measuredAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $measuredAtUtc,
            new DateTimeZone('UTC'),
        );

        return $measuredAt instanceof DateTimeImmutable
            && $measuredAt->format('Y-m-d') === $requestedDate;
    }
}
