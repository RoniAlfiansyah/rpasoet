<?php

namespace App\Services;

use App\Models\AdmiraltyAnalysisRunModel;
use App\Models\AdmiraltyDatasetModel;
use App\Models\AdmiraltyDatasetObservationModel;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class AdmiraltyCalculator
{
    /**
     * @return list<array{name: string, group: string, period_hours: float}>
     */
    private function componentDefinitions(): array
    {
        return [
            ['name' => 'S0',  'group' => 'Datum',         'period_hours' => 0.0],
            ['name' => 'M2',  'group' => 'Semidiurnal',   'period_hours' => 12.4206012],
            ['name' => 'S2',  'group' => 'Semidiurnal',   'period_hours' => 12.0000000],
            ['name' => 'N2',  'group' => 'Semidiurnal',   'period_hours' => 12.6583475],
            ['name' => 'K2',  'group' => 'Semidiurnal',   'period_hours' => 11.9672349],
            ['name' => 'K1',  'group' => 'Diurnal',       'period_hours' => 23.9344721],
            ['name' => 'O1',  'group' => 'Diurnal',       'period_hours' => 25.8193387],
            ['name' => 'P1',  'group' => 'Diurnal',       'period_hours' => 24.0658893],
            ['name' => 'M4',  'group' => 'Shallow water', 'period_hours' => 6.2103006],
            ['name' => 'MS4', 'group' => 'Shallow water', 'period_hours' => 6.1033393],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function prepareCalculationFromDataset(int $datasetId, string $modelName, array $options = []): array
    {
        $dataset      = (new AdmiraltyDatasetModel())->find($datasetId);
        $observations = (new AdmiraltyDatasetObservationModel())
            ->where('dataset_id', $datasetId)
            ->orderBy('observed_at', 'ASC')
            ->findAll();

        if (! is_array($dataset) || $dataset === []) {
            throw new RuntimeException('Dataset yang dipilih tidak ditemukan.');
        }

        if ($observations === []) {
            throw new RuntimeException('Observasi untuk dataset ini tidak ditemukan.');
        }

        return $this->buildPreparedCalculation($dataset, $observations, $modelName, $options);
    }

    /**
     * @return array<string, mixed>
     */
    public function generatePredictionFromRun(int $runId, string $startAt, string $endAt, int $intervalMinutes, string $forecastMode = 'admiralty_only', array $options = []): array
    {
        $run = (new AdmiraltyAnalysisRunModel())->find($runId);
        if (! is_array($run) || $run === []) {
            throw new RuntimeException('Run analisis tidak ditemukan.');
        }

        $modelName = (string) ($run['model_name'] ?? '');
        if ($modelName === 'admiralty_hidros') {
            throw new RuntimeException('Prediksi untuk model Admiralty Hidros belum diaktifkan. Saat ini prediksi hanya tersedia untuk Admiralty Hidro-Oseanografi Indonesia, Admiralty Cat A, dan Least Square.');
        }

        if (! in_array($modelName, ['least_square', 'admiralty_indonesia', 'admiralty_cat_a'], true)) {
            throw new RuntimeException('Prediksi saat ini baru tersedia untuk model Least Square, Admiralty Cat A, dan Admiralty Hidro-Oseanografi Indonesia.');
        }

        $dataset = (new AdmiraltyDatasetModel())->find((int) ($run['dataset_id'] ?? 0));
        if (! is_array($dataset) || $dataset === []) {
            throw new RuntimeException('Dataset untuk run ini tidak ditemukan.');
        }

        $result = json_decode((string) ($run['result_json'] ?? ''), true);
        if (! is_array($result)) {
            throw new RuntimeException('Hasil analisis run tidak valid untuk prediksi.');
        }
        $modelLabel = $this->resolveModelLabel($modelName);

        $timezoneName = is_string($dataset['timezone'] ?? null) && trim((string) ($dataset['timezone'] ?? '')) !== '' ? (string) $dataset['timezone'] : 'Asia/Jakarta';
        $timezone = new DateTimeZone($timezoneName);
        $start = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $startAt, $timezone);
        $end = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $endAt, $timezone);
        $reference = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) ($dataset['start_at'] ?? ''), $timezone);

        if (! $start instanceof DateTimeImmutable || ! $end instanceof DateTimeImmutable) {
            throw new RuntimeException('Format waktu prediksi tidak valid.');
        }

        if (! $reference instanceof DateTimeImmutable) {
            throw new RuntimeException('Waktu awal dataset tidak valid untuk prediksi.');
        }

        if ($start > $end) {
            throw new RuntimeException('Waktu mulai prediksi tidak boleh melebihi waktu akhir.');
        }

        if (! in_array($intervalMinutes, [10, 15, 30, 60], true)) {
            throw new RuntimeException('Interval prediksi harus 10, 15, 30, atau 60 menit.');
        }

        $components = is_array($result['component_targets'] ?? null) ? $result['component_targets'] : [];
        if ($components === []) {
            throw new RuntimeException('Komponen harmonik untuk run ini belum tersedia.');
        }
        $predictionAdjustment = $this->normalizePredictionAdjustmentOptions($options['prediction_adjustment'] ?? []);

        $phaseAlignment = null;
        if (in_array($modelName, ['admiralty_indonesia', 'admiralty_cat_a'], true)) {
            $observations = (new AdmiraltyDatasetObservationModel())
                ->where('dataset_id', (int) ($run['dataset_id'] ?? 0))
                ->orderBy('observed_at', 'ASC')
                ->findAll();
            [$preparedRowsForPhase] = $this->prepareObservationRows($observations, $timezone, (int) ($dataset['interval_minutes'] ?? 60));
            $phaseAlignment = $this->estimateGlobalPhaseOffset(
                $this->buildSeriesRows($preparedRowsForPhase),
                $components,
                (float) ($result['summary']['msl'] ?? 0.0)
            );
        }
        $components = $this->applyPredictionAdjustmentToComponents($components, $predictionAdjustment);

        $targetSeriesRows = $this->buildTargetSeriesRows($start, $end, $intervalMinutes, $reference);
        if ((float) ($predictionAdjustment['time_shift_hours'] ?? 0.0) !== 0.0) {
            $targetSeriesRows = $this->applyPredictionTimeShift($targetSeriesRows, (float) $predictionAdjustment['time_shift_hours']);
        }
        $baseForecastRows = $this->forecastAdmiralty(
            $targetSeriesRows,
            $components,
            (float) ($result['summary']['msl'] ?? 0.0),
            (float) ($phaseAlignment['phase_offset_deg'] ?? 0.0),
        );
        $modeApplied = 'admiralty_only';
        $predictionRows = $baseForecastRows;

        return [
            'meta' => [
                'run_code'          => (string) ($run['run_code'] ?? '-'),
                'model_name'        => $modelLabel,
                'station_name'      => (string) ($dataset['station_name'] ?? '-'),
                'timezone'          => $timezoneName,
                'interval_minutes'  => $intervalMinutes,
                'start_at'          => $start->format('d/m/Y H:i'),
                'end_at'            => $end->format('d/m/Y H:i'),
                'prediction_count'  => count($predictionRows),
                'offset_level'      => number_format($this->resolvePredictionOffset($components, (float) ($result['summary']['msl'] ?? 0.0)), 4, '.', ''),
                'prediction_status' => match ($modelName) {
                    'admiralty_indonesia' => $predictionAdjustment['is_active']
                        ? 'Prediksi berbasis konstanta workbook Admiralty Indonesia dengan adjustment aktif'
                        : 'Prediksi berbasis konstanta workbook Admiralty Indonesia',
                    'admiralty_cat_a' => $predictionAdjustment['is_active']
                        ? 'Prediksi berbasis ekstraksi harmonik Admiralty Cat A dengan adjustment aktif'
                        : 'Prediksi berbasis ekstraksi harmonik Admiralty Cat A dari observasi lapangan',
                    default => $predictionAdjustment['is_active']
                        ? 'Prediksi berbasis hasil Least Square dengan adjustment aktif'
                        : 'Prediksi berbasis hasil Least Square',
                },
                'phase_offset_deg'  => $phaseAlignment['phase_offset_deg'] ?? null,
                'phase_rmse_before' => $phaseAlignment['rmse_before'] ?? null,
                'phase_rmse_after'  => $phaseAlignment['rmse_after'] ?? null,
                'adjustment'        => $predictionAdjustment,
            ],
            'rows' => array_map(
                static fn (array $row): array => [
                    'datetime'    => (string) ($row['datetime_display'] ?? '-'),
                    'water_level' => number_format((float) ($row['predicted'] ?? 0.0), 4, '.', ''),
                ],
                $predictionRows,
            ),
            'debug' => null,
        ];
    }

    /**
     * @param array<string, mixed> $adjustment
     * @return array{amplitude_percent: float, phase_degrees: float, p1_amplitude_percent: float, p1_phase_degrees: float, time_shift_hours: float, is_active: bool}
     */
    private function normalizePredictionAdjustmentOptions(array $adjustment): array
    {
        $normalized = [
            'amplitude_percent' => is_numeric($adjustment['amplitude_percent'] ?? null) ? (float) $adjustment['amplitude_percent'] : 0.0,
            'phase_degrees' => is_numeric($adjustment['phase_degrees'] ?? null) ? (float) $adjustment['phase_degrees'] : 0.0,
            'p1_amplitude_percent' => is_numeric($adjustment['p1_amplitude_percent'] ?? null) ? (float) $adjustment['p1_amplitude_percent'] : 0.0,
            'p1_phase_degrees' => is_numeric($adjustment['p1_phase_degrees'] ?? null) ? (float) $adjustment['p1_phase_degrees'] : 0.0,
            'time_shift_hours' => is_numeric($adjustment['time_shift_hours'] ?? null) ? (float) $adjustment['time_shift_hours'] : 0.0,
            'is_active' => false,
        ];

        $normalized['is_active'] =
            abs($normalized['amplitude_percent']) > 0.0
            || abs($normalized['phase_degrees']) > 0.0
            || abs($normalized['p1_amplitude_percent']) > 0.0
            || abs($normalized['p1_phase_degrees']) > 0.0
            || abs($normalized['time_shift_hours']) > 0.0;

        return $normalized;
    }

    /**
     * @param list<array<string, mixed>> $components
     * @param array{amplitude_percent: float, phase_degrees: float, p1_amplitude_percent: float, p1_phase_degrees: float, time_shift_hours: float, is_active: bool} $adjustment
     * @return list<array<string, mixed>>
     */
    private function applyPredictionAdjustmentToComponents(array $components, array $adjustment): array
    {
        if (! ($adjustment['is_active'] ?? false)) {
            return $components;
        }

        $globalAmplitudeScale = 1 + (((float) $adjustment['amplitude_percent']) / 100);
        $p1AmplitudeScale = 1 + (((float) $adjustment['p1_amplitude_percent']) / 100);

        return array_map(static function (array $component) use ($adjustment, $globalAmplitudeScale, $p1AmplitudeScale): array {
            $name = strtoupper((string) ($component['name'] ?? ''));
            if ($name === 'S0') {
                return $component;
            }

            $amplitude = is_numeric($component['amplitude'] ?? null) ? (float) $component['amplitude'] : 0.0;
            $phase = is_numeric($component['phase'] ?? null) ? (float) $component['phase'] : 0.0;

            $amplitude *= $globalAmplitudeScale;
            $phase += (float) $adjustment['phase_degrees'];

            if ($name === 'P1') {
                $amplitude *= $p1AmplitudeScale;
                $phase += (float) $adjustment['p1_phase_degrees'];
            }

            $component['amplitude'] = number_format($amplitude, 4, '.', '');
            $component['phase'] = number_format(fmod($phase + 3600.0, 360.0), 2, '.', '');

            return $component;
        }, $components);
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @return list<array<string, mixed>>
     */
    private function applyPredictionTimeShift(array $seriesRows, float $timeShiftHours): array
    {
        return array_map(static function (array $row) use ($timeShiftHours): array {
            $row['time_hours'] = ((float) ($row['time_hours'] ?? 0.0)) - $timeShiftHours;

            return $row;
        }, $seriesRows);
    }

    /**
     * @param list<array<string, mixed>> $observations
     * @return array{0: list<array<string, mixed>>, 1: list<float>}
     */
    private function prepareObservationRows(array $observations, DateTimeZone $timezone, int $intervalMinutes): array
    {
        $levels = [];
        $prepared = [];

        foreach ($observations as $index => $row) {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) ($row['observed_at'] ?? ''), $timezone);
            $level = isset($row['water_level']) ? (float) $row['water_level'] : null;

            if (! $dateTime instanceof DateTimeImmutable || ! is_float($level)) {
                continue;
            }

            $levels[] = $level;
            $prepared[] = [
                'no'           => $index + 1,
                'datetime'     => $dateTime,
                'water_level'  => $level,
                'day_index'    => (int) floor($index / 24) + 1,
                'hour_index'   => (int) $dateTime->format('H'),
                'time_label'   => $dateTime->format('d/m/Y H:i:s'),
                'time_hours'   => round(($index * (float) $intervalMinutes) / 60, 4),
            ];
        }

        return [$prepared, $levels];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPreparedCalculation(array $dataset, array $observations, string $modelName, array $options = []): array
    {
        $timezoneName = is_string($dataset['timezone'] ?? null) && trim((string) $dataset['timezone']) !== '' ? (string) $dataset['timezone'] : 'Asia/Jakarta';
        $timezone     = new DateTimeZone($timezoneName);
        $prepared    = [];
        $modelLabel = $this->resolveModelLabel($modelName);
        $intervalMinutes = (int) ($dataset['interval_minutes'] ?? 60);
        [$prepared, $levels] = $this->prepareObservationRows($observations, $timezone, $intervalMinutes);

        if ($prepared === []) {
            throw new RuntimeException('Observasi valid tidak tersedia untuk tahap perhitungan.');
        }

        $msl            = array_sum($levels) / count($levels);
        $minLevel       = min($levels);
        $maxLevel       = max($levels);
        $durationHours  = count($prepared) > 1 ? (($intervalMinutes * (count($prepared) - 1)) / 60) : 0.0;
        $tidalRange     = $maxLevel - $minLevel;

        foreach ($prepared as &$row) {
            $row['deviation'] = round($row['water_level'] - $msl, 4);
        }
        unset($row);

        [$workingColumns, $workingTable, $notes, $componentTargets, $workingTableTitle, $subPanels] = $this->buildModelSpecificPayload(
            $modelName,
            $prepared,
            $dataset,
            $msl,
        );

        $resolvedMsl = $this->resolvePredictionOffset($componentTargets, $msl);

        return [
            'summary' => [
                'dataset_status' => $this->resolveDatasetStatusLabel($dataset, $modelLabel),
                'interval_label' => $intervalMinutes . ' menit',
                'interval_minutes' => $intervalMinutes,
                'data_count'     => count($prepared),
                'start_at'       => $this->formatDateTime($dataset['start_at'] ?? null, $timezone),
                'end_at'         => $this->formatDateTime($dataset['end_at'] ?? null, $timezone),
                'msl'            => round($resolvedMsl, 4),
                'min_level'      => round($minLevel, 4),
                'max_level'      => round($maxLevel, 4),
                'tidal_range'    => round($tidalRange, 4),
                'duration_hours' => round($durationHours, 2),
                'station_name'   => (string) ($dataset['station_name'] ?? '-'),
                'latitude'       => isset($dataset['latitude']) ? (float) $dataset['latitude'] : null,
                'longitude'      => isset($dataset['longitude']) ? (float) $dataset['longitude'] : null,
                'timezone'       => $timezoneName,
                'model_name'     => $modelName,
                'model_label'    => $modelLabel,
            ],
            'working_table_title' => $workingTableTitle,
            'working_columns'     => $workingColumns,
            'working_table'       => $workingTable,
            'component_targets'   => $componentTargets,
            'comparison_chart'    => $this->buildComparisonChart($prepared, $componentTargets, $resolvedMsl, $modelName, $modelLabel),
            'notes'               => $notes,
            'sub_panels'          => $subPanels,
            'calculation_stage'   => in_array($modelName, ['least_square', 'admiralty_cat_a'], true) ? 'harmonic_estimation_ready' : 'prepared',
        ];
    }

    /**
     * @param array<string, mixed> $dataset
     */
    private function resolveDatasetStatusLabel(array $dataset, string $modelLabel): string
    {
        $validationStatus = (string) ($dataset['validation_status'] ?? '');

        return match ($validationStatus) {
            'analysis_ready' => 'Layak analisa untuk tahap perhitungan ' . $modelLabel,
            'analysis_warning' => 'Layak dengan peringatan untuk tahap perhitungan ' . $modelLabel,
            'analysis_blocked' => 'Tidak layak analisa untuk tahap perhitungan ' . $modelLabel,
            default => 'Siap untuk tahap perhitungan ' . $modelLabel,
        };
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @param list<array<string, mixed>> $components
     * @return array<string, mixed>
     */
    private function buildComparisonChart(array $prepared, array $components, float $fallbackOffset, string $modelName, string $modelLabel): array
    {
        $windowRows = $this->buildSeriesRows(array_slice($prepared, 0, min(count($prepared), 15 * 24)));
        $labels = array_map(static fn (array $row): string => (string) $row['time_label'], $windowRows);
        $observed = array_map(static fn (array $row): float => round((float) $row['observed'], 4), $windowRows);
        $phaseAlignment = null;
        if (in_array($modelName, ['admiralty_indonesia', 'admiralty_hidros', 'admiralty_cat_a'], true)) {
            $phaseAlignment = $this->estimateGlobalPhaseOffset(
                $this->buildSeriesRows($prepared),
                $components,
                $fallbackOffset
            );
        }
        $baseRows = $this->forecastAdmiralty($windowRows, $components, $fallbackOffset);
        if ($phaseAlignment !== null) {
            $baseRows = $this->forecastAdmiralty(
                $windowRows,
                $components,
                $fallbackOffset,
                (float) ($phaseAlignment['phase_offset_deg'] ?? 0.0),
            );
        }
        $baseSeriesPoints = array_map(
            static fn (array $row): float => round((float) ($row['predicted'] ?? 0.0), 4),
            $baseRows,
        );
        $baseSeriesAvailable = $baseSeriesPoints !== [];

        $series = [[
            'model_name' => $modelName,
            'model_label' => $modelLabel,
            'points' => $baseSeriesPoints,
            'available' => $baseSeriesAvailable,
            'reason' => $baseSeriesAvailable
                ? 'Kurva harmonik tersedia untuk grafik perbandingan.'
                : 'Kurva harmonik belum tersedia untuk grafik perbandingan.',
        ]];
        $evaluations = [
            $this->evaluateForecast(
                $windowRows,
                $baseRows,
                $modelLabel,
                $modelName === 'least_square'
                    ? 'least_square_only'
                    : ($modelName === 'admiralty_indonesia' ? 'admiralty_only' : $modelName),
                'ok',
            ),
        ];
        return [
            'labels' => $labels,
            'observed' => [
                'label' => 'Dataset (Pengamatan)',
                'points' => $observed,
            ],
            'series' => $series,
            'point_count' => count($windowRows),
            'window_days' => 15,
            'evaluations' => $evaluations,
            'phase_alignment' => $phaseAlignment,
            'adjustment_basis' => in_array($modelName, ['admiralty_indonesia', 'admiralty_hidros', 'admiralty_cat_a', 'least_square'], true)
                ? $this->buildAdjustmentBasis(
                    $windowRows,
                    $components,
                    $fallbackOffset,
                    (float) ($phaseAlignment['phase_offset_deg'] ?? 0.0),
                )
                : null,
        ];
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     * @return list<array<string, mixed>>
     */
    private function forecastAdmiraltyExtension(array $seriesRows, array $components, float $fallbackOffset, float $phaseOffsetDeg = 0.0): array
    {
        return $this->forecastNp159($seriesRows, $components, $fallbackOffset, $phaseOffsetDeg);
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     * @return list<array<string, mixed>>
     */
    private function forecastNp159(array $seriesRows, array $components, float $fallbackOffset, float $phaseOffsetDeg = 0.0): array
    {
        $offset = $this->resolvePredictionOffset($components, $fallbackOffset);
        $rows = [];

        foreach ($seriesRows as $row) {
            $predicted = $offset;
            $timeHours = (float) ($row['time_hours'] ?? 0.0);

            foreach ($components as $component) {
                $periodHours = isset($component['period_hours']) ? (float) $component['period_hours'] : 0.0;
                $baseAmplitude = is_numeric($component['amplitude'] ?? null) ? (float) $component['amplitude'] : 0.0;
                $nodeFactor = is_numeric($component['node_factor'] ?? null) ? (float) $component['node_factor'] : 1.0;
                $phaseDegrees = (is_numeric($component['phase'] ?? null) ? (float) $component['phase'] : 0.0) + $phaseOffsetDeg;
                $phaseCorrection = is_numeric($component['phase_correction_deg'] ?? null) ? (float) $component['phase_correction_deg'] : 0.0;
                $equilibriumArgument = is_numeric($component['equilibrium_argument_deg'] ?? null) ? (float) $component['equilibrium_argument_deg'] : 0.0;

                if ($periodHours <= 0.0 || abs($baseAmplitude) <= 0.0) {
                    continue;
                }

                $omegaDegreesPerHour = 360.0 / $periodHours;
                $predicted += ($baseAmplitude * $nodeFactor) * cos(
                    deg2rad(($omegaDegreesPerHour * $timeHours) + $equilibriumArgument + $phaseCorrection - $phaseDegrees)
                );
            }

            $rows[] = [
                'datetime' => $row['datetime'] ?? null,
                'datetime_display' => (string) ($row['datetime_display'] ?? '-'),
                'time_label' => (string) ($row['time_label'] ?? ''),
                'time_hours' => $timeHours,
                'predicted' => $predicted,
            ];
        }

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     */
    private function estimateGlobalPhaseOffsetExtension(array $seriesRows, array $components, float $fallbackOffset): ?array
    {
        return $this->estimateGlobalPhaseOffsetNp159($seriesRows, $components, $fallbackOffset);
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     */
    private function estimateGlobalPhaseOffsetNp159(array $seriesRows, array $components, float $fallbackOffset): ?array
    {
        if (! $this->hasUsableNp159Components($components) || $seriesRows === []) {
            return null;
        }

        $observed = array_map(static fn (array $row): float => (float) ($row['observed'] ?? 0.0), $seriesRows);
        $baselineRows = $this->forecastNp159($seriesRows, $components, $fallbackOffset, 0.0);
        $baseline = array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $baselineRows);
        $bestOffset = 0.0;
        $bestRmse = $this->computeRMSE($observed, $baseline);

        for ($offset = -180; $offset <= 180; $offset += 5) {
            $predictedRows = $this->forecastNp159($seriesRows, $components, $fallbackOffset, $offset);
            $predicted = array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $predictedRows);
            $rmse = $this->computeRMSE($observed, $predicted);
            if ($rmse < $bestRmse) {
                $bestRmse = $rmse;
                $bestOffset = (float) $offset;
            }
        }

        for ($offset = $bestOffset - 4.0; $offset <= $bestOffset + 4.0; $offset += 0.5) {
            $predictedRows = $this->forecastNp159($seriesRows, $components, $fallbackOffset, $offset);
            $predicted = array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $predictedRows);
            $rmse = $this->computeRMSE($observed, $predicted);
            if ($rmse < $bestRmse) {
                $bestRmse = $rmse;
                $bestOffset = round($offset, 2);
            }
        }

        return [
            'phase_offset_deg' => $bestOffset,
            'rmse_before' => round($this->computeRMSE($observed, $baseline), 4),
            'rmse_after' => round($bestRmse, 4),
            'improved' => $bestRmse < $this->computeRMSE($observed, $baseline),
        ];
    }

    /**
     * @param list<array<string, mixed>> $components
     */
    private function resolvePredictionOffset(array $components, float $fallback): float
    {
        foreach ($components as $component) {
            if (strtoupper((string) ($component['name'] ?? '')) !== 'S0') {
                continue;
            }

            if (! isset($component['amplitude'])) {
                break;
            }

            return (float) $component['amplitude'];
        }

        return $fallback;
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return list<array<string, mixed>>
     */
    private function buildSeriesRows(array $prepared): array
    {
        return array_values(array_map(
            static fn (array $row): array => [
                'datetime' => $row['datetime'],
                'datetime_display' => $row['datetime'] instanceof DateTimeImmutable ? $row['datetime']->format('d/m/Y H:i') : '-',
                'time_label' => (string) ($row['time_label'] ?? ''),
                'time_hours' => (float) ($row['time_hours'] ?? 0.0),
                'observed' => (float) ($row['water_level'] ?? 0.0),
            ],
            $prepared,
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTargetSeriesRows(DateTimeImmutable $start, DateTimeImmutable $end, int $intervalMinutes, DateTimeImmutable $reference): array
    {
        $rows = [];
        $period = new \DatePeriod($start, new DateInterval('PT' . $intervalMinutes . 'M'), $end->add(new DateInterval('PT' . $intervalMinutes . 'M')));

        foreach ($period as $dateTime) {
            $rows[] = [
                'datetime' => $dateTime,
                'datetime_display' => $dateTime->format('d/m/Y H:i'),
                'time_label' => $dateTime->format('d/m/Y H:i:s'),
                'time_hours' => ($dateTime->getTimestamp() - $reference->getTimestamp()) / 3600,
            ];
        }

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     * @return list<array<string, mixed>>
     */
    private function forecastAdmiralty(array $seriesRows, array $components, float $fallbackOffset, float $phaseOffsetDeg = 0.0): array
    {
        $offset = $this->resolvePredictionOffset($components, $fallbackOffset);
        $rows = [];

        foreach ($seriesRows as $row) {
            $predicted = $offset;
            $timeHours = (float) ($row['time_hours'] ?? 0.0);

            foreach ($components as $component) {
                $periodHours = isset($component['period_hours']) ? (float) $component['period_hours'] : 0.0;
                $amplitude = is_numeric($component['amplitude'] ?? null) ? (float) $component['amplitude'] : 0.0;
                $phase = deg2rad((float) ($component['phase'] ?? 0.0) + $phaseOffsetDeg);

                if ($periodHours <= 0.0 || abs($amplitude) <= 0.0) {
                    continue;
                }

                $omega = 2 * M_PI / $periodHours;
                $predicted += $amplitude * cos(($omega * $timeHours) - $phase);
            }

            $rows[] = [
                'datetime' => $row['datetime'] ?? null,
                'datetime_display' => (string) ($row['datetime_display'] ?? '-'),
                'time_label' => (string) ($row['time_label'] ?? ''),
                'time_hours' => $timeHours,
                'predicted' => $predicted,
            ];
        }

        return $rows;
    }

    /**
     * @param list<float> $observed
     * @param list<float> $predicted
     */
    private function computeRMSE(array $observed, array $predicted): float
    {
        $count = min(count($observed), count($predicted));
        if ($count === 0) {
            return INF;
        }

        $sum = 0.0;
        for ($index = 0; $index < $count; $index++) {
            $diff = (float) $observed[$index] - (float) $predicted[$index];
            $sum += $diff * $diff;
        }

        return sqrt($sum / $count);
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     * @return array<string, mixed>|null
     */
    private function estimateGlobalPhaseOffset(array $seriesRows, array $components, float $fallbackOffset): ?array
    {
        if (! $this->hasUsablePeriodicComponents($components) || $seriesRows === []) {
            return null;
        }

        $observed = array_map(static fn (array $row): float => (float) ($row['observed'] ?? 0.0), $seriesRows);
        $baselineRows = $this->forecastAdmiralty($seriesRows, $components, $fallbackOffset, 0.0);
        $baseline = array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $baselineRows);
        $baselineRmse = $this->computeRMSE($observed, $baseline);

        $bestOffset = 0.0;
        $bestRmse = $baselineRmse;

        for ($offset = -180.0; $offset <= 180.0; $offset += 1.0) {
            $predictedRows = $this->forecastAdmiralty($seriesRows, $components, $fallbackOffset, $offset);
            $predicted = array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $predictedRows);
            $rmse = $this->computeRMSE($observed, $predicted);
            if ($rmse < $bestRmse) {
                $bestRmse = $rmse;
                $bestOffset = $offset;
            }
        }

        $refineStart = max(-180.0, $bestOffset - 1.0);
        $refineEnd = min(180.0, $bestOffset + 1.0);
        for ($offset = $refineStart; $offset <= $refineEnd + 0.0001; $offset += 0.1) {
            $predictedRows = $this->forecastAdmiralty($seriesRows, $components, $fallbackOffset, $offset);
            $predicted = array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $predictedRows);
            $rmse = $this->computeRMSE($observed, $predicted);
            if ($rmse < $bestRmse) {
                $bestRmse = $rmse;
                $bestOffset = round($offset, 1);
            }
        }

        return [
            'phase_offset_deg' => $bestOffset,
            'rmse_before' => round($baselineRmse, 4),
            'rmse_after' => round($bestRmse, 4),
            'improved' => $bestRmse < $baselineRmse,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hybridDefaults(array $overrides = []): array
    {
        $defaults = [
            'mode' => 'admiralty_only',
            'residual_weight' => 0.35,
            'max_residual_correction_meter' => 0.08,
            'training_window_hours' => 168,
            'minimum_training_points' => 72,
            'maximum_gap_minutes' => 90,
        ];

        $config = array_replace($defaults, is_array($overrides) ? $overrides : []);

        return [
            'mode' => 'admiralty_only',
            'residual_weight' => max(0.0, min(1.0, (float) ($config['residual_weight'] ?? $defaults['residual_weight']))),
            'max_residual_correction_meter' => max(0.01, (float) ($config['max_residual_correction_meter'] ?? $defaults['max_residual_correction_meter'])),
            'training_window_hours' => max(24, (int) ($config['training_window_hours'] ?? $defaults['training_window_hours'])),
            'minimum_training_points' => max(24, (int) ($config['minimum_training_points'] ?? $defaults['minimum_training_points'])),
            'maximum_gap_minutes' => max(30, (int) ($config['maximum_gap_minutes'] ?? $defaults['maximum_gap_minutes'])),
        ];
    }

    /**
     * @return list<array{name: string, group: string, period_hours: float}>
     */
    private function residualComponentDefinitions(): array
    {
        return array_values(array_filter(
            $this->componentDefinitions(),
            static fn (array $component): bool => in_array($component['name'], ['M2', 'S2', 'N2', 'K1', 'O1'], true),
        ));
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array{name: string, group: string, period_hours: float}> $definitions
     * @return array<string, mixed>
     */
    private function fitHarmonicLeastSquares(array $seriesRows, array $definitions, string $statusLabel, string $valueKey = 'observed', bool $includeIntercept = true): array
    {
        $parameterCount = ($includeIntercept ? 1 : 0) + (count($definitions) * 2);
        $normalMatrix = array_fill(0, $parameterCount, array_fill(0, $parameterCount, 0.0));
        $rhs = array_fill(0, $parameterCount, 0.0);
        $rows = [];

        foreach ($seriesRows as $row) {
            if (! array_key_exists($valueKey, $row) || ! is_numeric($row[$valueKey])) {
                continue;
            }

            $timeHours = (float) ($row['time_hours'] ?? 0.0);
            $observed = (float) $row[$valueKey];
            $designRow = $includeIntercept ? [1.0] : [];

            foreach ($definitions as $component) {
                $omega = 2 * M_PI / (float) $component['period_hours'];
                $designRow[] = cos($omega * $timeHours);
                $designRow[] = sin($omega * $timeHours);
            }

            $rows[] = ['design' => $designRow, 'observed' => $observed];

            for ($i = 0; $i < $parameterCount; $i++) {
                $rhs[$i] += $designRow[$i] * $observed;
                for ($j = 0; $j < $parameterCount; $j++) {
                    $normalMatrix[$i][$j] += $designRow[$i] * $designRow[$j];
                }
            }
        }

        if ($rows === []) {
            throw new RuntimeException('Tidak ada data yang cukup untuk fitting least squares.');
        }

        $coefficients = $this->solveLinearSystem($normalMatrix, $rhs);
        $residualSum = 0.0;

        foreach ($rows as $row) {
            $predicted = 0.0;
            foreach ($row['design'] as $index => $value) {
                $predicted += $coefficients[$index] * $value;
            }
            $residual = $row['observed'] - $predicted;
            $residualSum += $residual * $residual;
        }

        $resultComponents = [];
        $coefficientOffset = 0;
        if ($includeIntercept) {
            $resultComponents[] = [
                'name' => 'S0',
                'group' => 'Datum',
                'period_hours' => 0.0,
                'amplitude' => number_format((float) $coefficients[0], 4, '.', ''),
                'phase' => '0.00',
                'status' => $statusLabel . ' (offset)',
            ];
            $coefficientOffset = 1;
        }

        foreach ($definitions as $index => $component) {
            $cosCoefficient = (float) $coefficients[$coefficientOffset + ($index * 2)];
            $sinCoefficient = (float) $coefficients[$coefficientOffset + 1 + ($index * 2)];
            $amplitude = sqrt(($cosCoefficient * $cosCoefficient) + ($sinCoefficient * $sinCoefficient));
            $phaseDegrees = fmod(rad2deg(atan2($sinCoefficient, $cosCoefficient)) + 360.0, 360.0);

            $resultComponents[] = [
                'name' => $component['name'],
                'group' => $component['group'],
                'period_hours' => $component['period_hours'],
                'amplitude' => number_format($amplitude, 4, '.', ''),
                'phase' => number_format($phaseDegrees, 2, '.', ''),
                'status' => $statusLabel,
            ];
        }

        return [
            'components' => $resultComponents,
            'residual_rms' => sqrt($residualSum / max(count($rows), 1)),
            'point_count' => count($rows),
        ];
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $baseForecastRows
     * @return array<string, mixed>
     */
    private function fitResidualLeastSquares(array $seriesRows, array $baseForecastRows, array $config): array
    {
        $baseMap = [];
        foreach ($baseForecastRows as $row) {
            $baseMap[(string) ($row['time_label'] ?? '')] = $row;
        }

        $maxDateTime = null;
        foreach ($seriesRows as $row) {
            if (($row['datetime'] ?? null) instanceof DateTimeImmutable) {
                $maxDateTime = $row['datetime'];
            }
        }

        $componentNames = array_map(static fn (array $row): string => (string) $row['name'], $this->residualComponentDefinitions());

        if (! $maxDateTime instanceof DateTimeImmutable) {
            return [
                'valid' => false,
                'fallback_reason' => 'Timestamp observasi tidak valid untuk training hybrid.',
                'components_used' => $componentNames,
            ];
        }

        $trainingStart = $maxDateTime->sub(new DateInterval('PT' . (int) $config['training_window_hours'] . 'H'));
        $alignedRows = [];
        foreach ($seriesRows as $row) {
            $label = (string) ($row['time_label'] ?? '');
            $dateTime = $row['datetime'] ?? null;
            if (! $dateTime instanceof DateTimeImmutable || $dateTime < $trainingStart || ! isset($baseMap[$label])) {
                continue;
            }
            $alignedRows[] = [
                'datetime' => $dateTime,
                'time_label' => $label,
                'time_hours' => (float) ($row['time_hours'] ?? 0.0),
                'residual' => (float) ($row['observed'] ?? 0.0) - (float) ($baseMap[$label]['predicted'] ?? 0.0),
            ];
        }

        if (count($alignedRows) < (int) $config['minimum_training_points']) {
            return [
                'valid' => false,
                'fallback_reason' => 'Jumlah data training residual belum memenuhi minimum.',
                'training_point_count' => count($alignedRows),
                'training_window_start' => $trainingStart->format('d/m/Y H:i'),
                'training_window_end' => $maxDateTime->format('d/m/Y H:i'),
                'components_used' => $componentNames,
            ];
        }

        for ($index = 1, $length = count($alignedRows); $index < $length; $index++) {
            $gapMinutes = (($alignedRows[$index]['datetime']->getTimestamp() - $alignedRows[$index - 1]['datetime']->getTimestamp()) / 60);
            if ($gapMinutes > (int) $config['maximum_gap_minutes']) {
                return [
                    'valid' => false,
                    'fallback_reason' => 'Gap data pada training window terlalu besar untuk model residual.',
                    'training_point_count' => count($alignedRows),
                    'training_window_start' => $trainingStart->format('d/m/Y H:i'),
                    'training_window_end' => $maxDateTime->format('d/m/Y H:i'),
                    'components_used' => $componentNames,
                ];
            }
        }

        try {
            $fit = $this->fitHarmonicLeastSquares($alignedRows, $this->residualComponentDefinitions(), 'Residual Least Square konservatif', 'residual', true);
        } catch (RuntimeException $e) {
            return [
                'valid' => false,
                'fallback_reason' => 'Fitting residual gagal secara numerik.',
                'training_point_count' => count($alignedRows),
                'training_window_start' => $trainingStart->format('d/m/Y H:i'),
                'training_window_end' => $maxDateTime->format('d/m/Y H:i'),
                'components_used' => $componentNames,
            ];
        }

        $guardrailAmplitude = (float) $config['max_residual_correction_meter'] / max((float) $config['residual_weight'], 1.0e-6);
        $periodicAmplitudes = [];
        $dominantComponent = null;
        foreach ($fit['components'] as $component) {
            if (strtoupper((string) ($component['name'] ?? '')) === 'S0') {
                continue;
            }
            $periodicAmplitudes[(string) $component['name']] = (float) ($component['amplitude'] ?? 0.0);
            if ($dominantComponent === null || (float) ($component['amplitude'] ?? 0.0) > (float) ($dominantComponent['amplitude'] ?? 0.0)) {
                $dominantComponent = [
                    'name' => (string) ($component['name'] ?? '-'),
                    'amplitude' => (float) ($component['amplitude'] ?? 0.0),
                ];
            }
            if ((float) ($component['amplitude'] ?? 0.0) > $guardrailAmplitude) {
                return [
                    'valid' => false,
                    'fallback_reason' => 'Amplitudo residual melebihi guardrail internal.',
                    'training_point_count' => count($alignedRows),
                    'training_window_start' => $trainingStart->format('d/m/Y H:i'),
                    'training_window_end' => $maxDateTime->format('d/m/Y H:i'),
                    'components_used' => $componentNames,
                    'periodic_amplitudes' => $periodicAmplitudes,
                    'guardrail_amplitude' => $guardrailAmplitude,
                    'dominant_component' => $dominantComponent,
                ];
            }
        }

        return [
            'valid' => true,
            'fallback_reason' => '',
            'components' => $fit['components'],
            'training_point_count' => count($alignedRows),
            'training_window_start' => $trainingStart->format('d/m/Y H:i'),
            'training_window_end' => $maxDateTime->format('d/m/Y H:i'),
            'components_used' => $componentNames,
            'periodic_amplitudes' => $periodicAmplitudes,
            'residual_rms' => (float) ($fit['residual_rms'] ?? 0.0),
            'clamp_limit' => (float) $config['max_residual_correction_meter'],
            'guardrail_amplitude' => $guardrailAmplitude,
            'dominant_component' => $dominantComponent,
        ];
    }

    /**
     * @param list<array<string, mixed>> $targetSeriesRows
     * @param list<array<string, mixed>> $baseForecastRows
     * @return array<string, mixed>
     */
    private function forecastHybrid(array $targetSeriesRows, array $baseForecastRows, array $residualFit, array $config): array
    {
        if (! (bool) ($residualFit['valid'] ?? false)) {
            return [
                'used_hybrid' => false,
                'rows' => $baseForecastRows,
                'debug' => [
                    'mode_requested' => 'hybrid',
                    'mode_active' => 'admiralty_only',
                    'training_point_count' => (int) ($residualFit['training_point_count'] ?? 0),
                    'training_window_start' => (string) ($residualFit['training_window_start'] ?? '-'),
                    'training_window_end' => (string) ($residualFit['training_window_end'] ?? '-'),
                    'components_used' => $residualFit['components_used'] ?? [],
                    'periodic_amplitudes' => $residualFit['periodic_amplitudes'] ?? [],
                    'guardrail_amplitude' => $residualFit['guardrail_amplitude'] ?? null,
                    'dominant_component' => $residualFit['dominant_component'] ?? null,
                    'clamp_active' => false,
                    'fallback' => true,
                    'fallback_reason' => (string) ($residualFit['fallback_reason'] ?? 'Residual model tidak valid.'),
                ],
            ];
        }

        $baseMap = [];
        foreach ($baseForecastRows as $row) {
            $baseMap[(string) ($row['time_label'] ?? '')] = $row;
        }

        $residualRows = $this->forecastAdmiralty($targetSeriesRows, is_array($residualFit['components'] ?? null) ? $residualFit['components'] : [], 0.0);
        $residualMap = [];
        foreach ($residualRows as $row) {
            $residualMap[(string) ($row['time_label'] ?? '')] = $row;
        }

        $rows = [];
        $clampActive = false;
        foreach ($targetSeriesRows as $row) {
            $label = (string) ($row['time_label'] ?? '');
            $base = (float) ($baseMap[$label]['predicted'] ?? 0.0);
            $rawCorrection = (float) ($residualMap[$label]['predicted'] ?? 0.0) * (float) $config['residual_weight'];
            $clampedCorrection = max(-1 * (float) $config['max_residual_correction_meter'], min((float) $config['max_residual_correction_meter'], $rawCorrection));
            if (abs($clampedCorrection - $rawCorrection) > 1.0e-9) {
                $clampActive = true;
            }
            $rows[] = [
                'datetime' => $row['datetime'] ?? null,
                'datetime_display' => (string) ($row['datetime_display'] ?? '-'),
                'time_label' => $label,
                'time_hours' => (float) ($row['time_hours'] ?? 0.0),
                'predicted' => $base + $clampedCorrection,
                'correction' => $clampedCorrection,
            ];
        }

        return [
            'used_hybrid' => true,
            'rows' => $rows,
            'debug' => [
                'mode_requested' => 'hybrid',
                'mode_active' => 'hybrid',
                'training_point_count' => (int) ($residualFit['training_point_count'] ?? 0),
                'training_window_start' => (string) ($residualFit['training_window_start'] ?? '-'),
                'training_window_end' => (string) ($residualFit['training_window_end'] ?? '-'),
                'components_used' => $residualFit['components_used'] ?? [],
                'periodic_amplitudes' => $residualFit['periodic_amplitudes'] ?? [],
                'guardrail_amplitude' => $residualFit['guardrail_amplitude'] ?? null,
                'dominant_component' => $residualFit['dominant_component'] ?? null,
                'clamp_active' => $clampActive,
                'fallback' => false,
                'fallback_reason' => '',
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $forecastRows
     * @return array<string, mixed>
     */
    private function evaluateForecast(array $seriesRows, array $forecastRows, string $label, string $mode, string $status, array $debug = []): array
    {
        $forecastMap = [];
        foreach ($forecastRows as $row) {
            $forecastMap[(string) ($row['time_label'] ?? '')] = (float) ($row['predicted'] ?? 0.0);
        }

        $errors = [];
        $peakError = null;
        $lowError = null;
        $peakObserved = null;
        $lowObserved = null;

        foreach ($seriesRows as $row) {
            $labelKey = (string) ($row['time_label'] ?? '');
            if (! array_key_exists($labelKey, $forecastMap)) {
                continue;
            }
            $observed = (float) ($row['observed'] ?? 0.0);
            $predicted = $forecastMap[$labelKey];
            $error = $predicted - $observed;
            $errors[] = $error;
            if ($peakObserved === null || $observed > $peakObserved) {
                $peakObserved = $observed;
                $peakError = $error;
            }
            if ($lowObserved === null || $observed < $lowObserved) {
                $lowObserved = $observed;
                $lowError = $error;
            }
        }

        if ($errors === []) {
            return ['label' => $label, 'mode' => $mode, 'status' => 'unavailable', 'point_count' => 0];
        }

        $count = count($errors);
        $mae = array_sum(array_map(static fn (float $value): float => abs($value), $errors)) / $count;
        $bias = array_sum($errors) / $count;
        $rmse = sqrt(array_sum(array_map(static fn (float $value): float => $value * $value, $errors)) / $count);

        return [
            'label' => $label,
            'mode' => $mode,
            'status' => $status,
            'point_count' => $count,
            'rmse' => round($rmse, 4),
            'mae' => round($mae, 4),
            'bias' => round($bias, 4),
            'peak_error' => $peakError !== null ? round((float) $peakError, 4) : null,
            'low_error' => $lowError !== null ? round((float) $lowError, 4) : null,
            'debug' => $debug,
        ];
    }

    /**
     * @param list<array<string, mixed>> $components
     */
    private function hasUsablePeriodicComponents(array $components): bool
    {
        foreach ($components as $component) {
            $periodHours = isset($component['period_hours']) ? (float) $component['period_hours'] : 0.0;
            $amplitude = is_numeric($component['amplitude'] ?? null) ? (float) $component['amplitude'] : 0.0;
            if ($periodHours > 0.0 && abs($amplitude) > 0.0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $components
     */
    private function hasUsableNp159Components(array $components): bool
    {
        foreach ($components as $component) {
            $periodHours = isset($component['period_hours']) ? (float) $component['period_hours'] : 0.0;
            $amplitude = is_numeric($component['amplitude'] ?? null) ? (float) $component['amplitude'] : 0.0;

            if ($periodHours <= 0.0 || abs($amplitude) <= 0.0) {
                continue;
            }

            if (
                ! is_numeric($component['node_factor'] ?? null)
                || ! is_numeric($component['phase_correction_deg'] ?? null)
                || ! is_numeric($component['equilibrium_argument_deg'] ?? null)
            ) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function formatDateTime($value, DateTimeZone $timezone): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '-';
        }

        $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, $timezone);

        return $parsed instanceof DateTimeImmutable ? $parsed->format('d/m/Y H:i:s') : '-';
    }

    private function resolveModelLabel(string $modelName): string
    {
        return match ($modelName) {
            'admiralty_klasik'    => 'Admiralty Klasik',
            'admiralty_indonesia' => 'Admiralty Hidro-Oseanografi Indonesia',
            'admiralty_hidros'    => 'Admiralty Hidros',
            'admiralty_cat_a'     => 'Admiralty Cat A',
            'least_square'        => 'Least Square',
            default               => 'Model Tidak Dikenal',
        };
    }

    /**
     * @param list<array<string, mixed>> $components
     * @param list<array<string, mixed>> $skema7Rows
     * @return list<array<string, mixed>>
     */
    private function applyWorkbookAstronomicsToComponents(array $components, array $skema7Rows): array
    {
        $rowsByLabel = [];
        foreach ($skema7Rows as $row) {
            $label = strtolower(trim((string) ($row['label'] ?? '')));
            if ($label !== '') {
                $rowsByLabel[$label] = $row;
            }
        }

        $aliases = [
            'k2' => 's2',
            'p1' => 'k1',
        ];

        return array_map(function (array $component) use ($rowsByLabel, $aliases): array {
            $name = strtolower((string) ($component['name'] ?? ''));
            $sourceName = $name;
            if (isset($aliases[$name])) {
                $sourceName = $aliases[$name];
            }

            $component['node_factor'] = number_format($this->readWorkbookAstronomicValue($rowsByLabel, 'tabel 5 : f', $sourceName, 1.0), 4, '.', '');
            $component['phase_correction_deg'] = number_format($this->readWorkbookAstronomicValue($rowsByLabel, 'tabel 9 : u', $sourceName, 0.0), 2, '.', '');
            $component['equilibrium_argument_deg'] = number_format($this->readWorkbookAstronomicValue($rowsByLabel, 'v', $sourceName, 0.0), 2, '.', '');

            return $component;
        }, $components);
    }

    /**
     * @param array<string, array<string, mixed>> $rowsByLabel
     */
    private function readWorkbookAstronomicValue(array $rowsByLabel, string $label, string $componentKey, float $default): float
    {
        $row = $rowsByLabel[$label] ?? null;
        if (! is_array($row)) {
            return $default;
        }

        $value = $row[$componentKey] ?? null;

        return $this->normalizeWorkbookNumeric($value, $default);
    }

    private function normalizeWorkbookNumeric($value, float $default = 0.0): float
    {
        if ($value === null) {
            return $default;
        }

        $text = trim((string) $value);
        if ($text === '' || $text === '-') {
            return $default;
        }

        $negative = false;
        if (str_starts_with($text, '(') && str_ends_with($text, ')')) {
            $negative = true;
            $text = substr($text, 1, -1);
        }

        $text = str_replace([' ', '.'], '', $text);
        $text = str_replace(',', '.', $text);

        if (! is_numeric($text)) {
            return $default;
        }

        $number = (float) $text;

        return $negative ? -$number : $number;
    }

    /**
     * @param list<array<string, mixed>> $seriesRows
     * @param list<array<string, mixed>> $components
     * @return array<string, mixed>
     */
    private function buildAdjustmentBasis(array $seriesRows, array $components, float $fallbackOffset, float $phaseOffsetDeg): array
    {
        return [
            'offset' => round($this->resolvePredictionOffset($components, $fallbackOffset), 6),
            'phase_offset_deg' => round($phaseOffsetDeg, 4),
            'rows' => array_map(static function (array $row): array {
                return [
                    'time_hours' => round((float) ($row['time_hours'] ?? 0.0), 6),
                    'time_label' => (string) ($row['time_label'] ?? ''),
                ];
            }, $seriesRows),
            'components' => array_values(array_map(static function (array $component): array {
                return [
                    'name' => (string) ($component['name'] ?? ''),
                    'period_hours' => round((float) ($component['period_hours'] ?? 0.0), 8),
                    'amplitude' => round((float) ($component['amplitude'] ?? 0.0), 6),
                    'phase' => round((float) ($component['phase'] ?? 0.0), 6),
                ];
            }, array_values(array_filter(
                $components,
                static fn (array $component): bool => (float) ($component['period_hours'] ?? 0.0) > 0.0
                    && abs((float) ($component['amplitude'] ?? 0.0)) > 0.0,
            )))),
        ];
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{0: list<array{key: string, label: string}>, 1: list<array<string, string>>, 2: list<string>, 3: list<array{name: string, group: string, status: string}>, 4: string, 5: list<array<string, mixed>>}
     */
    private function buildModelSpecificPayload(string $modelName, array $prepared, array $dataset, float $msl): array
    {
        return match ($modelName) {
            'admiralty_klasik'    => $this->buildAdmiraltyKlasikPayload($prepared),
            'admiralty_hidros'    => $this->buildAdmiraltyHidrosPayload($prepared, $dataset, $msl),
            'admiralty_cat_a'     => $this->buildAdmiraltyCatAPayload($prepared),
            'least_square'        => $this->buildLeastSquarePayload($prepared),
            default               => $this->buildAdmiraltyIndonesiaPayload($prepared, $dataset, $msl),
        };
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{0: list<array{key: string, label: string}>, 1: list<array<string, string>>, 2: list<string>, 3: list<array{name: string, group: string, status: string}>, 4: string, 5: list<array<string, mixed>>}
     */
    private function buildAdmiraltyHidrosPayload(array $prepared, array $dataset, float $msl): array
    {
        $workingColumns = [
            ['key' => 'tgl', 'label' => 'Tanggal'],
        ];
        for ($hour = 0; $hour < 24; $hour++) {
            $workingColumns[] = [
                'key' => 'h' . str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
            ];
        }

        $workingTable = [];
        $notes = [
            'Model aktif: Admiralty Hidros.',
            'Model ini memakai workbook Admiralty Hidros sebagai engine Excel terpisah dari Admiralty Hidro-Oseanografi Indonesia.',
            'Untuk v1, fokusnya adalah pembacaan konstanta harmonik, tabel kerja workbook, dan perbandingan hasil antar model.',
            'Prediksi untuk model Admiralty Hidros belum diaktifkan.',
        ];
        $componentTargets = $this->defaultComponentTargets('Menunggu hasil workbook Admiralty Hidros', $msl);
        $subPanels = [];

        try {
            $workbookResult = (new AdmiraltyExcelEngine('hidros'))->calculate($dataset, $prepared, $this->componentDefinitions());
            $workbookTables = is_array($workbookResult['tables'] ?? null) ? $workbookResult['tables'] : [];
            $componentTargets = is_array($workbookResult['components'] ?? null) && $workbookResult['components'] !== []
                ? $workbookResult['components']
                : $componentTargets;

            if (is_array($workbookTables['matrix'] ?? null) && $workbookTables['matrix'] !== []) {
                $workingTable = $workbookTables['matrix'];
            }

            if (is_array($workbookTables['harmonics'] ?? null) && $workbookTables['harmonics'] !== []) {
                $subPanels[] = [
                    'title' => 'Konstanta Harmonik',
                    'description' => 'Tabel komponen harmonik hasil pembacaan langsung dari workbook Admiralty Hidros.',
                    'columns' => [
                        ['key' => 'name', 'label' => 'Komponen'],
                        ['key' => 'amplitude_cm', 'label' => 'Amplitudo (cm)'],
                        ['key' => 'phase_deg', 'label' => 'Fase (deg)'],
                    ],
                    'rows' => $workbookTables['harmonics'],
                    'items' => [
                        'Angka amplitudo mentah pada workbook Admiralty Hidros tersimpan dalam sentimeter.',
                        'Di tabel Komponen Target aplikasi, amplitudo sudah dikonversi ke meter agar setara dengan model lain.',
                    ],
                ];
            }

            if (is_array($workbookTables['derivation'] ?? null) && $workbookTables['derivation'] !== []) {
                $subPanels[] = [
                    'title' => 'Turunan Rumus',
                    'description' => 'Catatan turunan dan relasi komponen yang dibaca dari blok workbook Admiralty Hidros.',
                    'columns' => [
                        ['key' => 'item', 'label' => 'Item'],
                        ['key' => 'note', 'label' => 'Catatan'],
                    ],
                    'rows' => $workbookTables['derivation'],
                    'items' => [
                        'Panel ini membantu audit logika turunan Admiralty Hidros tanpa memaksa format skema Indonesia.',
                    ],
                ];
            }

            if (is_array($workbookTables['classification'] ?? null) && $workbookTables['classification'] !== []) {
                $subPanels[] = [
                    'title' => 'Klasifikasi',
                    'description' => 'Ringkasan bilangan formzahl dan teks klasifikasi pasut dari workbook Admiralty Hidros.',
                    'columns' => [
                        ['key' => 'item', 'label' => 'Item'],
                        ['key' => 'value', 'label' => 'Nilai'],
                        ['key' => 'note', 'label' => 'Keterangan'],
                    ],
                    'rows' => $workbookTables['classification'],
                    'items' => [
                        'Blok ini dipakai sebagai pembanding karakter pasut terhadap model lain.',
                    ],
                ];
            }

            $engineMeta = is_array($workbookResult['engine_meta'] ?? null) ? $workbookResult['engine_meta'] : [];
            $subPanels[] = [
                'title' => 'Engine Workbook',
                'description' => 'Metadata engine workbook yang dipakai untuk model Admiralty Hidros.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'Sumber', 'value' => (string) ($engineMeta['source'] ?? 'Excel Workbook Admiralty Hidros')],
                    ['item' => 'Template', 'value' => 'Template workbook Admiralty Hidros aktif'],
                    ['item' => 'Workbook Copy', 'value' => (string) ($engineMeta['workbook_copy'] ?? '-')],
                    ['item' => 'Variant', 'value' => (string) ($engineMeta['engine_variant'] ?? 'hidros')],
                ],
                'items' => [
                    'Engine Hidros dipisahkan dari workbook Indonesia agar dua metode Admiralty tetap independen.',
                    'Model Hidros saat ini hanya mendukung perhitungan harmonik dan audit tabel workbook.',
                ],
            ];

            $resolvedDatum = $this->resolvePredictionOffset($componentTargets, $msl);
            $subPanels[] = [
                'title' => 'Konsistensi Datum',
                'description' => 'Perbandingan S0 workbook Admiralty Hidros terhadap MSL observasi.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'MSL observasi', 'value' => number_format($msl, 4, '.', '')],
                    ['item' => 'S0 workbook', 'value' => number_format($resolvedDatum, 4, '.', '')],
                    ['item' => 'Selisih datum', 'value' => number_format($resolvedDatum - $msl, 4, '.', '')],
                    ['item' => 'Status', 'value' => abs($resolvedDatum - $msl) <= 0.0500 ? 'Selaras' : 'Perlu ditinjau'],
                ],
                'items' => [
                    'Datum model Hidros dibaca dari komponen S0 workbook Admiralty Hidros.',
                ],
            ];
        } catch (RuntimeException $exception) {
            $workingTable = [];
            $componentTargets = $this->defaultComponentTargets('Workbook Admiralty Hidros belum berhasil diproses', $msl);
            $subPanels[] = [
                'title' => 'Engine Workbook',
                'description' => 'Engine Excel Admiralty Hidros belum berhasil dijalankan.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'Status', 'value' => 'Workbook Admiralty Hidros gagal diproses'],
                    ['item' => 'Pesan', 'value' => $exception->getMessage()],
                ],
                'items' => [
                    'Model Hidros tidak memakai fallback perhitungan internal. Hasil akhir baru dianggap tersedia jika workbook berhasil dibaca.',
                ],
            ];
        }

        return [
            $workingColumns,
            $workingTable,
            $notes,
            $componentTargets,
            'Matriks 29 Piantan Admiralty Hidros',
            $subPanels,
        ];
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{0: list<array{key: string, label: string}>, 1: list<array<string, string>>, 2: list<string>, 3: list<array{name: string, group: string, status: string}>, 4: string, 5: list<array<string, mixed>>}
     */
    private function buildAdmiraltyIndonesiaPayload(array $prepared, array $dataset, float $msl): array
    {
        $multipliers = [
            'x1' => [-1, -1, -1, -1, -1, -1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, -1, -1, -1, -1, -1, -1],
            'y1' => [-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
            'x2' => [1, 1, 1, -1, -1, -1, -1, -1, -1, 1, 1, 1, 1, 1, 1, -1, -1, -1, -1, -1, -1, 1, 1, 1],
            'y2' => [1, 1, 1, 1, 1, 1, -1, -1, -1, -1, -1, -1, 1, 1, 1, 1, 1, 1, -1, -1, -1, -1, -1, -1],
            'x4' => [1, 0, -1, -1, 0, 1, 1, 0, -1, -1, 0, 1, 1, 0, -1, -1, 0, 1, 1, 0, -1, -1, 0, 1],
            'y4' => [1, 1, 1, -1, -1, -1, 1, 1, 1, -1, -1, -1, 1, 1, 1, -1, -1, -1, 1, 1, 1, -1, -1, -1],
        ];

        $dailyBuckets = [];
        foreach ($prepared as $row) {
            $dayIndex = (int) $row['day_index'];
            $hourIndex = (int) $row['hour_index'];
            $level = (float) $row['water_level'];

            if (! isset($dailyBuckets[$dayIndex])) {
                $dailyBuckets[$dayIndex] = [
                    'date'   => substr((string) $row['time_label'], 0, 10),
                    'levels' => [],
                    'hours'  => [],
                ];
            }

            $dailyBuckets[$dayIndex]['levels'][] = $level;
            $dailyBuckets[$dayIndex]['hours'][$hourIndex] = $level;
        }

        $skemaIColumns = [
            ['key' => 'day_index', 'label' => 'Hari'],
            ['key' => 'date', 'label' => 'Tanggal'],
        ];
        for ($hour = 0; $hour < 24; $hour++) {
            $skemaIColumns[] = ['key' => 'h' . str_pad((string) $hour, 2, '0', STR_PAD_LEFT), 'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT)];
        }

        $skemaIIColumns = [
            ['key' => 'day_index', 'label' => 'Hari'],
            ['key' => 'date', 'label' => 'Tanggal'],
            ['key' => 'x0', 'label' => 'X0'],
            ['key' => 'x1_plus', 'label' => 'X1 +'],
            ['key' => 'x1_minus', 'label' => 'X1 -'],
            ['key' => 'y1_plus', 'label' => 'Y1 +'],
            ['key' => 'y1_minus', 'label' => 'Y1 -'],
            ['key' => 'x2_plus', 'label' => 'X2 +'],
            ['key' => 'x2_minus', 'label' => 'X2 -'],
            ['key' => 'y2_plus', 'label' => 'Y2 +'],
            ['key' => 'y2_minus', 'label' => 'Y2 -'],
            ['key' => 'x4_plus', 'label' => 'X4 +'],
            ['key' => 'x4_minus', 'label' => 'X4 -'],
            ['key' => 'y4_plus', 'label' => 'Y4 +'],
            ['key' => 'y4_minus', 'label' => 'Y4 -'],
        ];

        $skemaIIIColumns = [
            ['key' => 'day_index', 'label' => 'Hari'],
            ['key' => 'date', 'label' => 'Tanggal'],
            ['key' => 'x0', 'label' => 'X0'],
            ['key' => 'x1', 'label' => 'X1'],
            ['key' => 'y1', 'label' => 'Y1'],
            ['key' => 'x2', 'label' => 'X2'],
            ['key' => 'y2', 'label' => 'Y2'],
            ['key' => 'x4', 'label' => 'X4'],
            ['key' => 'y4', 'label' => 'Y4'],
        ];

        $rows = array_map(
            static function (int $dayIndex, array $bucket) use ($msl, $multipliers): array {
                $levels = $bucket['levels'];
                $dailyMean = array_sum($levels) / max(count($levels), 1);
                $dailyRange = max($levels) - min($levels);
                $hourValues = [];
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourValues[$hour] = isset($bucket['hours'][$hour]) ? (float) $bucket['hours'][$hour] : 0.0;
                }

                $x0 = array_sum($hourValues);
                $seriesResults = [];
                foreach ($multipliers as $key => $series) {
                    $positive = 0.0;
                    $negative = 0.0;
                    foreach ($series as $hour => $factor) {
                        $product = $hourValues[$hour] * $factor;
                        if ($product > 0) {
                            $positive += $product;
                        } elseif ($product < 0) {
                            $negative += abs($product);
                        }
                    }
                    $seriesResults[$key . '_plus'] = $positive;
                    $seriesResults[$key . '_minus'] = $negative;
                    $seriesResults[$key] = $positive - $negative;
                }

                $result = [
                    'day_index'  => (string) $dayIndex,
                    'date'       => (string) $bucket['date'],
                    'x0'         => number_format($x0, 4, '.', ''),
                    'daily_mean' => number_format($dailyMean, 4, '.', ''),
                    'daily_range'=> number_format($dailyRange, 4, '.', ''),
                    'dev_msl'    => number_format($dailyMean - $msl, 4, '.', ''),
                    'x1_plus'    => number_format($seriesResults['x1_plus'], 4, '.', ''),
                    'x1_minus'   => number_format($seriesResults['x1_minus'], 4, '.', ''),
                    'y1_plus'    => number_format($seriesResults['y1_plus'], 4, '.', ''),
                    'y1_minus'   => number_format($seriesResults['y1_minus'], 4, '.', ''),
                    'x2_plus'    => number_format($seriesResults['x2_plus'], 4, '.', ''),
                    'x2_minus'   => number_format($seriesResults['x2_minus'], 4, '.', ''),
                    'y2_plus'    => number_format($seriesResults['y2_plus'], 4, '.', ''),
                    'y2_minus'   => number_format($seriesResults['y2_minus'], 4, '.', ''),
                    'x4_plus'    => number_format($seriesResults['x4_plus'], 4, '.', ''),
                    'x4_minus'   => number_format($seriesResults['x4_minus'], 4, '.', ''),
                    'y4_plus'    => number_format($seriesResults['y4_plus'], 4, '.', ''),
                    'y4_minus'   => number_format($seriesResults['y4_minus'], 4, '.', ''),
                    'x1'         => number_format($seriesResults['x1'], 4, '.', ''),
                    'y1'         => number_format($seriesResults['y1'], 4, '.', ''),
                    'x2'         => number_format($seriesResults['x2'], 4, '.', ''),
                    'y2'         => number_format($seriesResults['y2'], 4, '.', ''),
                    'x4'         => number_format($seriesResults['x4'], 4, '.', ''),
                    'y4'         => number_format($seriesResults['y4'], 4, '.', ''),
                ];

                for ($hour = 0; $hour < 24; $hour++) {
                    $result['h' . str_pad((string) $hour, 2, '0', STR_PAD_LEFT)] = number_format($hourValues[$hour], 4, '.', '');
                }

                return $result;
            },
            array_keys($dailyBuckets),
            array_values($dailyBuckets),
        );

        $dailyRows = array_slice($rows, 0, 29);
        $semiSeries = array_values(array_filter(array_map(
            static fn (array $row): ?float => isset($row['x1']) ? (float) $row['x1'] : null,
            $dailyRows,
        ), static fn ($value): bool => $value !== null));
        $diurnalSeries = array_values(array_filter(array_map(
            static fn (array $row): ?float => isset($row['y1']) ? (float) $row['y1'] : null,
            $dailyRows,
        ), static fn ($value): bool => $value !== null));
        $rangeSeries = array_values(array_map(
            static fn (array $row): float => (float) $row['daily_range'],
            $dailyRows,
        ));

        $skemaIVColumns = [
            ['key' => 'index', 'label' => 'Indeks'],
            ['key' => 'source', 'label' => 'Sumber'],
            ['key' => 'x_value', 'label' => 'X'],
            ['key' => 'y_value', 'label' => 'Y'],
        ];
        $sumField = static function (array $rows, string $field): float {
            return array_sum(array_map(static fn (array $row): float => (float) ($row[$field] ?? 0.0), $rows));
        };
        $skemaIVRows = [
            [
                'index' => '00 +',
                'source' => 'X0',
                'x_value' => number_format($sumField($dailyRows, 'x0'), 4, '.', ''),
                'y_value' => '-',
            ],
            [
                'index' => '10 +',
                'source' => 'X1 / Y1',
                'x_value' => number_format($sumField($dailyRows, 'x1_plus'), 4, '.', ''),
                'y_value' => number_format($sumField($dailyRows, 'y1_plus'), 4, '.', ''),
            ],
            [
                'index' => '10 -',
                'source' => 'X1 / Y1',
                'x_value' => number_format($sumField($dailyRows, 'x1_minus'), 4, '.', ''),
                'y_value' => number_format($sumField($dailyRows, 'y1_minus'), 4, '.', ''),
            ],
            [
                'index' => '20 +',
                'source' => 'X2 / Y2',
                'x_value' => number_format($sumField($dailyRows, 'x2_plus'), 4, '.', ''),
                'y_value' => number_format($sumField($dailyRows, 'y2_plus'), 4, '.', ''),
            ],
            [
                'index' => '20 -',
                'source' => 'X2 / Y2',
                'x_value' => number_format($sumField($dailyRows, 'x2_minus'), 4, '.', ''),
                'y_value' => number_format($sumField($dailyRows, 'y2_minus'), 4, '.', ''),
            ],
            [
                'index' => '40 +',
                'source' => 'X4 / Y4',
                'x_value' => number_format($sumField($dailyRows, 'x4_plus'), 4, '.', ''),
                'y_value' => number_format($sumField($dailyRows, 'y4_plus'), 4, '.', ''),
            ],
            [
                'index' => '40 -',
                'source' => 'X4 / Y4',
                'x_value' => number_format($sumField($dailyRows, 'x4_minus'), 4, '.', ''),
                'y_value' => number_format($sumField($dailyRows, 'y4_minus'), 4, '.', ''),
            ],
        ];

        $semiAbsMean = $this->seriesAbsMean($semiSeries);
        $diurnalAbsMean = $this->seriesAbsMean($diurnalSeries);
        $rangeAbsMean = $this->seriesAbsMean($rangeSeries);
        $x0Total = $sumField($dailyRows, 'x0');
        $x1Total = $sumField($dailyRows, 'x1');
        $y1Total = $sumField($dailyRows, 'y1');
        $x2Total = $sumField($dailyRows, 'x2');
        $y2Total = $sumField($dailyRows, 'y2');
        $x4Total = $sumField($dailyRows, 'x4');
        $y4Total = $sumField($dailyRows, 'y4');
        $rowCount = max(count($dailyRows), 1);
        $skema56Columns = [
            ['key' => 'source', 'label' => 'Sumber'],
            ['key' => 'base_value', 'label' => 'Besaran'],
            ['key' => 's0', 'label' => 'S0'],
            ['key' => 'm2', 'label' => 'M2'],
            ['key' => 's2', 'label' => 'S2'],
            ['key' => 'n2', 'label' => 'N2'],
            ['key' => 'k2', 'label' => 'K2'],
            ['key' => 'k1', 'label' => 'K1'],
            ['key' => 'o1', 'label' => 'O1'],
            ['key' => 'p1', 'label' => 'P1'],
            ['key' => 'm4', 'label' => 'M4'],
            ['key' => 'ms4', 'label' => 'MS4'],
        ];
        $skema56Rows = [
            [
                'source' => 'X00',
                'base_value' => number_format($x0Total, 4, '.', ''),
                's0' => number_format($x0Total, 4, '.', ''),
                'm2' => '0.0000',
                's2' => '0.0000',
                'n2' => '0.0000',
                'k2' => '0.0000',
                'k1' => '0.0000',
                'o1' => '0.0000',
                'p1' => '0.0000',
                'm4' => '0.0000',
                'ms4' => '0.0000',
            ],
            [
                'source' => 'X10',
                'base_value' => number_format($x1Total, 4, '.', ''),
                's0' => '0.0000',
                'm2' => '0.0000',
                's2' => '0.0000',
                'n2' => '0.0000',
                'k2' => '0.0000',
                'k1' => number_format($x1Total, 4, '.', ''),
                'o1' => number_format($x1Total * 0.08, 4, '.', ''),
                'p1' => number_format($x1Total * 0.15, 4, '.', ''),
                'm4' => '0.0000',
                'ms4' => '0.0000',
            ],
            [
                'source' => 'Y10',
                'base_value' => number_format($y1Total, 4, '.', ''),
                's0' => '0.0000',
                'm2' => '0.0000',
                's2' => '0.0000',
                'n2' => '0.0000',
                'k2' => '0.0000',
                'k1' => number_format($y1Total, 4, '.', ''),
                'o1' => number_format($y1Total * 0.08, 4, '.', ''),
                'p1' => number_format($y1Total * 0.15, 4, '.', ''),
                'm4' => '0.0000',
                'ms4' => '0.0000',
            ],
            [
                'source' => 'X20',
                'base_value' => number_format($x2Total, 4, '.', ''),
                's0' => '0.0000',
                'm2' => number_format($x2Total * 0.03, 4, '.', ''),
                's2' => number_format($x2Total, 4, '.', ''),
                'n2' => number_format($x2Total * 0.03, 4, '.', ''),
                'k2' => number_format($x2Total * 0.15, 4, '.', ''),
                'k1' => '0.0000',
                'o1' => '0.0000',
                'p1' => '0.0000',
                'm4' => '0.0000',
                'ms4' => '0.0000',
            ],
            [
                'source' => 'Y20',
                'base_value' => number_format($y2Total, 4, '.', ''),
                's0' => '0.0000',
                'm2' => number_format($y2Total * 0.03, 4, '.', ''),
                's2' => number_format($y2Total, 4, '.', ''),
                'n2' => number_format($y2Total * 0.03, 4, '.', ''),
                'k2' => number_format($y2Total * 0.15, 4, '.', ''),
                'k1' => '0.0000',
                'o1' => '0.0000',
                'p1' => '0.0000',
                'm4' => '0.0000',
                'ms4' => '0.0000',
            ],
            [
                'source' => 'X40',
                'base_value' => number_format($x4Total, 4, '.', ''),
                's0' => '0.0000',
                'm2' => '0.0000',
                's2' => '0.0000',
                'n2' => '0.0000',
                'k2' => '0.0000',
                'k1' => '0.0000',
                'o1' => '0.0000',
                'p1' => '0.0000',
                'm4' => number_format($x4Total, 4, '.', ''),
                'ms4' => number_format($x4Total * 0.35, 4, '.', ''),
            ],
            [
                'source' => 'Y40',
                'base_value' => number_format($y4Total, 4, '.', ''),
                's0' => '0.0000',
                'm2' => '0.0000',
                's2' => '0.0000',
                'n2' => '0.0000',
                'k2' => '0.0000',
                'k1' => '0.0000',
                'o1' => '0.0000',
                'p1' => '0.0000',
                'm4' => number_format($y4Total, 4, '.', ''),
                'ms4' => number_format($y4Total * 0.35, 4, '.', ''),
            ],
        ];
        $componentSeeds = [
            'S0' => ['x' => $msl, 'y' => 0.0, 'f' => 1.0000, 'v' => 0.00, 'u' => 0.00, 'w' => 0.00],
            'M2' => ['x' => $x2Total * 0.03, 'y' => $y2Total * 0.03, 'f' => 1.0000, 'v' => 28.98, 'u' => 0.00, 'w' => 1.00],
            'S2' => ['x' => $x2Total, 'y' => $y2Total, 'f' => 1.0000, 'v' => 30.00, 'u' => 0.00, 'w' => 1.00],
            'N2' => ['x' => $x2Total * 0.03, 'y' => $y2Total * 0.03, 'f' => 1.0000, 'v' => 27.42, 'u' => 0.00, 'w' => 1.00],
            'K2' => ['x' => $x2Total * 0.15, 'y' => $y2Total * 0.15, 'f' => 1.0000, 'v' => 30.08, 'u' => 0.00, 'w' => 1.00],
            'K1' => ['x' => $x1Total, 'y' => $y1Total, 'f' => 1.0000, 'v' => 15.04, 'u' => 0.00, 'w' => 1.00],
            'O1' => ['x' => $x1Total * 0.08, 'y' => $y1Total * 0.08, 'f' => 1.0000, 'v' => 13.94, 'u' => 0.00, 'w' => 1.00],
            'P1' => ['x' => $x1Total * 0.15, 'y' => $y1Total * 0.15, 'f' => 1.0000, 'v' => 14.96, 'u' => 0.00, 'w' => 1.00],
            'M4' => ['x' => $x4Total, 'y' => $y4Total, 'f' => 1.0000, 'v' => 57.96, 'u' => 0.00, 'w' => 2.00],
            'MS4' => ['x' => $x4Total * 0.35, 'y' => $y4Total * 0.35, 'f' => 1.0000, 'v' => 58.98, 'u' => 0.00, 'w' => 2.00],
        ];
        $skema7ColumnsSummary = [
            ['key' => 'component', 'label' => 'Komponen'],
            ['key' => 'pr_cos_r', 'label' => 'PR cos r'],
            ['key' => 'pr_sin_r', 'label' => 'PR sin r'],
            ['key' => 'pr', 'label' => 'PR'],
            ['key' => 'f', 'label' => 'f'],
            ['key' => 'v', 'label' => 'V'],
            ['key' => 'u', 'label' => 'u'],
            ['key' => 'r', 'label' => 'r'],
            ['key' => 'w', 'label' => '1+W'],
            ['key' => 'status', 'label' => 'Status'],
        ];
        $skema7SummaryRows = array_map(
            static function (string $name, array $seed) use ($rowCount): array {
                $xValue = (float) $seed['x'];
                $yValue = (float) $seed['y'];
                $pr = $name === 'S0'
                    ? $xValue
                    : sqrt(($xValue * $xValue) + ($yValue * $yValue)) / $rowCount;
                $phase = $name === 'S0'
                    ? 0.0
                    : fmod((rad2deg(atan2($yValue, $xValue)) + 360.0), 360.0);

                return [
                    'component' => $name,
                    'pr_cos_r' => number_format($xValue / $rowCount, 4, '.', ''),
                    'pr_sin_r' => number_format($yValue / $rowCount, 4, '.', ''),
                    'pr' => number_format($pr, 4, '.', ''),
                    'f' => number_format((float) $seed['f'], 4, '.', ''),
                    'v' => number_format((float) $seed['v'], 2, '.', ''),
                    'u' => number_format((float) $seed['u'], 2, '.', ''),
                    'r' => number_format($phase, 2, '.', ''),
                    'w' => number_format((float) $seed['w'], 2, '.', ''),
                    'status' => $name === 'S0'
                        ? 'Datum rata-rata siap dipakai'
                        : 'Estimasi awal dari Skema 5&6',
                ];
            },
            array_keys($componentSeeds),
            array_values($componentSeeds),
        );

        $indonesiaComponentTargets = array_map(
            static function (array $component) use ($componentSeeds, $msl, $semiAbsMean, $diurnalAbsMean, $rangeAbsMean, $rowCount): array {
                $seed = $componentSeeds[$component['name']] ?? ['x' => 0.0, 'y' => 0.0];
                $xValue = (float) ($seed['x'] ?? 0.0);
                $yValue = (float) ($seed['y'] ?? 0.0);
                $amplitude = $component['name'] === 'S0'
                    ? $msl
                    : sqrt(($xValue * $xValue) + ($yValue * $yValue)) / $rowCount;
                $phase = $component['name'] === 'S0'
                    ? 0.0
                    : fmod((rad2deg(atan2($yValue, $xValue)) + 360.0), 360.0);
                $status = match ($component['group']) {
                    'Datum' => 'S0 siap sebagai datum rata-rata',
                    'Semidiurnal' => $semiAbsMean > 0 ? 'Estimasi awal semidiurnal dari Skema 5&6' : 'Indikator semidiurnal belum cukup',
                    'Diurnal' => $diurnalAbsMean > 0 ? 'Estimasi awal diurnal dari Skema 5&6' : 'Indikator diurnal belum cukup',
                    'Shallow water' => $rangeAbsMean > 0 ? 'Estimasi awal shallow water dari Skema 5&6' : 'Indikator shallow water belum cukup',
                    default => 'Menunggu faktor tabel Admiralty Indonesia',
                };

                return [
                    'name' => $component['name'],
                    'group' => $component['group'],
                    'period_hours' => $component['period_hours'],
                    'amplitude' => number_format($amplitude, 4, '.', ''),
                    'phase' => number_format($phase, 2, '.', ''),
                    'node_factor' => number_format((float) ($seed['f'] ?? 1.0), 4, '.', ''),
                    'phase_correction_deg' => number_format((float) ($seed['u'] ?? 0.0), 2, '.', ''),
                    'equilibrium_argument_deg' => '0.00',
                    'status' => $status,
                ];
            },
            $this->componentDefinitions(),
        );

        $subPanels = [
            [
                'title' => 'Skema 2',
                'description' => 'Penyusunan hasil penghitungan harga X1, Y1, X2, Y2, X4, dan Y4 dari matriks 24 jam menggunakan multiplier Tabel 2.',
                'columns' => $skemaIIColumns,
                'rows' => array_map(
                    static fn (array $row): array => [
                        'day_index' => $row['day_index'],
                        'date' => $row['date'],
                        'x0' => $row['x0'],
                        'x1_plus' => $row['x1_plus'],
                        'x1_minus' => $row['x1_minus'],
                        'y1_plus' => $row['y1_plus'],
                        'y1_minus' => $row['y1_minus'],
                        'x2_plus' => $row['x2_plus'],
                        'x2_minus' => $row['x2_minus'],
                        'y2_plus' => $row['y2_plus'],
                        'y2_minus' => $row['y2_minus'],
                        'x4_plus' => $row['x4_plus'],
                        'x4_minus' => $row['x4_minus'],
                        'y4_plus' => $row['y4_plus'],
                        'y4_minus' => $row['y4_minus'],
                    ],
                    $dailyRows,
                ),
                'items' => [
                    'X0 dihitung sebagai jumlah 24 bacaan harian.',
                    'Kolom + dan - dibentuk dari hasil perkalian bacaan dengan multiplier Admiralty Tabel 2.',
                ],
            ],
            [
                'title' => 'Skema 3',
                'description' => 'Penyusunan hasil perhitungan harga X dan Y indeks ke satu dari Skema 2 melalui selisih plus dan minus.',
                'columns' => $skemaIIIColumns,
                'rows' => array_map(
                    static fn (array $row): array => [
                        'day_index' => $row['day_index'],
                        'date' => $row['date'],
                        'x0' => $row['x0'],
                        'x1' => $row['x1'],
                        'y1' => $row['y1'],
                        'x2' => $row['x2'],
                        'y2' => $row['y2'],
                        'x4' => $row['x4'],
                        'y4' => $row['y4'],
                    ],
                    $dailyRows,
                ),
                'items' => [
                    'X1 = X1(+) - X1(-), demikian pula Y1, X2, Y2, X4, dan Y4.',
                    'Tahap ini adalah jembatan langsung menuju penggabungan indeks pada Skema 4.',
                ],
            ],
            [
                'title' => 'Skema 4',
                'description' => 'Penggabungan indeks X/Y dari Skema 3 menjadi besaran teragregasi untuk tahap berikutnya.',
                'columns' => $skemaIVColumns,
                'rows' => $skemaIVRows,
                'items' => [
                    'Versi digital ini menampilkan agregasi inti X/Y per kelompok indeks agar alur Skema 4 lebih operasional.',
                    'Workbook acuan memuat label indeks yang lebih rinci; itu bisa kita turunkan bertahap setelah relasi Tabel 6-7 selesai ditanam.',
                ],
            ],
            [
                'title' => 'Skema 5',
                'description' => 'Blok PR cos r dari workbook untuk penyusunan besaran X konstanta pasut 29 piantan.',
                'columns' => $skema56Columns,
                'rows' => $skema56Rows,
                'items' => [
                    'Pada workbook acuan, Skema 5 dan 6 dipakai bersama untuk menyusun besaran X dan Y dari konstanta pasut.',
                    'Panel ini difokuskan ke sisi PR cos r agar alur workbook lebih mudah diikuti.',
                ],
            ],
            [
                'title' => 'Skema 7',
                'description' => 'Rekap besaran PR, f, V, u, r, dan 1+W sebagai jembatan ke amplitudo dan fase akhir.',
                'columns' => $skema7ColumnsSummary,
                'rows' => $skema7SummaryRows,
                'items' => [
                    'Nilai yang tampil masih berupa estimasi awal berbasis hasil agregasi Skema 5&6.',
                    'Skema 7 pada workbook acuan merangkum PR, P, f, V, u, r, w, dan 1+W.',
                    'Konstanta harmonik final tetap menunggu implementasi tabel faktor Admiralty Indonesia yang lengkap.',
                ],
            ],
            [
                'title' => 'Forecasting Pasut',
                'description' => 'Rangkaian konstanta dan kontribusi komponen yang dipakai dalam persamaan eta(t) untuk prediksi pasut.',
                'columns' => [
                    ['key' => 'symbol', 'label' => 'Simbol'],
                    ['key' => 'meaning', 'label' => 'Makna'],
                ],
                'rows' => [
                    ['symbol' => 'η(t)', 'meaning' => 'Elevasi pasut sebagai fungsi waktu'],
                    ['symbol' => 'S0', 'meaning' => 'Duduk tengah / Mean Sea Level'],
                    ['symbol' => 'Ai', 'meaning' => 'Amplitudo komponen ke-i'],
                    ['symbol' => 'gi', 'meaning' => 'Fase komponen ke-i'],
                    ['symbol' => 'wi', 'meaning' => 'Frekuensi sudut komponen ke-i'],
                ],
                'items' => [
                    'Sheet workbook menunjukkan forecasting dibangun dari S0, Ai, gi, dan wi.',
                    'Jika engine workbook berhasil, cuplikan nilai eta(t) akan dibaca langsung dari sheet Forcasting Pasut.',
                ],
            ],
            [
                'title' => 'Tabel Faktor Admiralty',
                'description' => 'Struktur acuan tabel faktor yang dibutuhkan sebelum konstanta harmonik Admiralty Indonesia dihitung final.',
                'columns' => [
                    ['key' => 'table_name', 'label' => 'Tabel'],
                    ['key' => 'target_group', 'label' => 'Kelompok Target'],
                    ['key' => 'usage', 'label' => 'Pemakaian'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => [
                    [
                        'table_name' => 'Tabel 3',
                        'target_group' => 'Semidiurnal utama',
                        'usage' => 'Dipakai untuk M2, S2, N2 pada tahap turunan komponen semidiurnal.',
                        'status' => 'Struktur siap, koefisien numerik belum ditanam',
                    ],
                    [
                        'table_name' => 'Tabel 4',
                        'target_group' => 'Semidiurnal/Diurnal',
                        'usage' => 'Dipakai untuk K2 serta komponen diurnal bersama koreksi f, V, u.',
                        'status' => 'Struktur siap, koefisien numerik belum ditanam',
                    ],
                    [
                        'table_name' => 'Tabel 5',
                        'target_group' => 'Shallow water',
                        'usage' => 'Dipakai untuk M4 dan MS4 bersama koreksi f, V, u.',
                        'status' => 'Struktur siap, koefisien numerik belum ditanam',
                    ],
                ],
                'items' => [
                    'Tahap berikutnya adalah memasukkan nilai koefisien dari tabel faktor sesuai acuan Admiralty Indonesia.',
                    'Setelah tabel faktor tersedia, Skema V-VIII bisa diturunkan menjadi amplitudo dan fase final.',
                ],
            ],
        ];

        try {
            $workbookResult = (new AdmiraltyExcelEngine())->calculate($dataset, $prepared, $this->componentDefinitions());
            if (is_array($workbookResult['components'] ?? null)) {
                $indonesiaComponentTargets = $workbookResult['components'];
            }
            $workbookTables = is_array($workbookResult['tables'] ?? null) ? $workbookResult['tables'] : [];
            if (is_array($workbookTables['skema1'] ?? null) && $workbookTables['skema1'] !== []) {
                $skemaIColumns = [
                    ['key' => 'day_index', 'label' => 'No'],
                    ['key' => 'date', 'label' => 'Tanggal'],
                    ['key' => 'h00', 'label' => '00'],
                    ['key' => 'h01', 'label' => '01'],
                    ['key' => 'h02', 'label' => '02'],
                    ['key' => 'h03', 'label' => '03'],
                    ['key' => 'h04', 'label' => '04'],
                    ['key' => 'h05', 'label' => '05'],
                    ['key' => 'h06', 'label' => '06'],
                    ['key' => 'h07', 'label' => '07'],
                    ['key' => 'h08', 'label' => '08'],
                    ['key' => 'h09', 'label' => '09'],
                    ['key' => 'h10', 'label' => '10'],
                    ['key' => 'h11', 'label' => '11'],
                    ['key' => 'h12', 'label' => '12'],
                    ['key' => 'h13', 'label' => '13'],
                    ['key' => 'h14', 'label' => '14'],
                    ['key' => 'h15', 'label' => '15'],
                    ['key' => 'h16', 'label' => '16'],
                    ['key' => 'h17', 'label' => '17'],
                    ['key' => 'h18', 'label' => '18'],
                    ['key' => 'h19', 'label' => '19'],
                    ['key' => 'h20', 'label' => '20'],
                    ['key' => 'h21', 'label' => '21'],
                    ['key' => 'h22', 'label' => '22'],
                    ['key' => 'h23', 'label' => '23'],
                    ['key' => 'total', 'label' => 'Jumlah'],
                    ['key' => 'mean', 'label' => 'Rata2'],
                ];
                $dailyRows = $workbookTables['skema1'];
            }
            if (is_array($workbookTables['skema2'] ?? null)) {
                foreach ($subPanels as $index => $panel) {
                    if (($panel['title'] ?? '') === 'Skema 2') {
                        $subPanels[$index]['columns'] = [
                            ['key' => 'date', 'label' => 'Tanggal'],
                            ['key' => 'x0', 'label' => 'X0'],
                            ['key' => 'x1_plus', 'label' => 'X1 +'],
                            ['key' => 'x1_minus', 'label' => 'X1 -'],
                            ['key' => 'y1_plus', 'label' => 'Y1 +'],
                            ['key' => 'y1_minus', 'label' => 'Y1 -'],
                            ['key' => 'x2_plus', 'label' => 'X2 +'],
                            ['key' => 'x2_minus', 'label' => 'X2 -'],
                            ['key' => 'y2_plus', 'label' => 'Y2 +'],
                            ['key' => 'y2_minus', 'label' => 'Y2 -'],
                            ['key' => 'x4_plus', 'label' => 'X4 +'],
                            ['key' => 'x4_minus', 'label' => 'X4 -'],
                            ['key' => 'y4_plus', 'label' => 'Y4 +'],
                            ['key' => 'y4_minus', 'label' => 'Y4 -'],
                        ];
                        $subPanels[$index]['rows'] = $workbookTables['skema2'];
                        $subPanels[$index]['items'] = [
                            'Skema 2 ini dibaca langsung dari sheet skemA2 workbook.',
                            'Isi tabel memperlihatkan X0 serta pasangan plus-minus untuk X1, Y1, X2, Y2, X4, dan Y4.',
                        ];
                        break;
                    }
                }
            }
            if (is_array($workbookTables['skema3'] ?? null)) {
                foreach ($subPanels as $index => $panel) {
                    if (($panel['title'] ?? '') === 'Skema 3') {
                        $subPanels[$index]['columns'] = [
                            ['key' => 'date', 'label' => 'Tanggal'],
                            ['key' => 'x0', 'label' => 'X0'],
                            ['key' => 'x1', 'label' => 'X1'],
                            ['key' => 'y1', 'label' => 'Y1'],
                            ['key' => 'x2', 'label' => 'X2'],
                            ['key' => 'y2', 'label' => 'Y2'],
                            ['key' => 'x4', 'label' => 'X4'],
                            ['key' => 'y4', 'label' => 'Y4'],
                        ];
                        $subPanels[$index]['rows'] = $workbookTables['skema3'];
                        $subPanels[$index]['items'] = [
                            'Skema 3 ini dibaca langsung dari sheet skemA3 workbook.',
                            'Tabel menampilkan hasil selisih akhir X1, Y1, X2, Y2, X4, dan Y4 untuk tiap hari.',
                        ];
                        break;
                    }
                }
            }
            if (is_array($workbookTables['skema4'] ?? null)) {
                foreach ($subPanels as $index => $panel) {
                    if (($panel['title'] ?? '') === 'Skema 4') {
                        $subPanels[$index]['columns'] = [
                            ['key' => 'index_code', 'label' => 'Indeks'],
                            ['key' => 'sign', 'label' => 'Tanda'],
                            ['key' => 'value_x', 'label' => 'Harga X'],
                            ['key' => 'value_y', 'label' => 'Harga Y'],
                            ['key' => 'x', 'label' => 'X'],
                            ['key' => 'y', 'label' => 'Y'],
                        ];
                        $subPanels[$index]['rows'] = $workbookTables['skema4'];
                        $subPanels[$index]['items'] = [
                            'Skema 4 ini dibaca langsung dari sheet skemA4 workbook.',
                            'Panel memperlihatkan indeks, tanda, besarnya harga, lalu hasil X dan Y agregat per blok indeks.',
                        ];
                        break;
                    }
                }
            }
            if (is_array($workbookTables['skema56_cos'] ?? null)) {
                $subPanels[3]['columns'] = [
                    ['key' => 'label', 'label' => 'Baris'],
                    ['key' => 'base', 'label' => 'Besaran'],
                    ['key' => 's0', 'label' => 'S0'],
                    ['key' => 'm2', 'label' => 'M2'],
                    ['key' => 's2', 'label' => 'S2'],
                    ['key' => 'n2', 'label' => 'N2'],
                    ['key' => 'k1', 'label' => 'K1'],
                    ['key' => 'o1', 'label' => 'O1'],
                    ['key' => 'm4', 'label' => 'M4'],
                    ['key' => 'ms4', 'label' => 'MS4'],
                ];
                $subPanels[3]['rows'] = $workbookTables['skema56_cos'];
                $subPanels[3]['items'] = [
                    'Bagian ini sekarang membaca blok Skema 5 (PR cos r) langsung dari workbook.',
                ];
            }
            if (is_array($workbookTables['skema56_sin'] ?? null)) {
                array_splice($subPanels, 4, 0, [[
                    'title' => 'Skema 6',
                    'description' => 'Blok PR sin r hasil pembacaan langsung dari workbook Excel Admiralty.',
                    'columns' => [
                        ['key' => 'label', 'label' => 'Baris'],
                        ['key' => 'base', 'label' => 'Besaran'],
                        ['key' => 's0', 'label' => 'S0'],
                        ['key' => 'm2', 'label' => 'M2'],
                        ['key' => 's2', 'label' => 'S2'],
                        ['key' => 'n2', 'label' => 'N2'],
                        ['key' => 'k1', 'label' => 'K1'],
                        ['key' => 'o1', 'label' => 'O1'],
                        ['key' => 'm4', 'label' => 'M4'],
                        ['key' => 'ms4', 'label' => 'MS4'],
                    ],
                    'rows' => $workbookTables['skema56_sin'],
                    'items' => [
                        'Blok ini berasal dari Skema 6 pada workbook dan dipakai untuk PR sin r.',
                    ],
                ]]);
            }
            if (is_array($workbookTables['skema56_total'] ?? null)) {
                array_splice($subPanels, 5, 0, [[
                    'title' => 'Rekap Skema 5&6',
                    'description' => 'Rekap total PR cos r dan PR sin r hasil workbook sebelum masuk ke Skema 7.',
                    'columns' => [
                        ['key' => 'label', 'label' => 'Baris'],
                        ['key' => 's0', 'label' => 'S0'],
                        ['key' => 'm2', 'label' => 'M2'],
                        ['key' => 's2', 'label' => 'S2'],
                        ['key' => 'n2', 'label' => 'N2'],
                        ['key' => 'k1', 'label' => 'K1'],
                        ['key' => 'o1', 'label' => 'O1'],
                        ['key' => 'm4', 'label' => 'M4'],
                        ['key' => 'ms4', 'label' => 'MS4'],
                    ],
                    'rows' => $workbookTables['skema56_total'],
                    'items' => [
                        'Baris total ini dibaca langsung dari workbook sebagai masukan utama Skema 7.',
                    ],
                ]]);
            }
            if (is_array($workbookTables['skema7'] ?? null)) {
                $indonesiaComponentTargets = $this->applyWorkbookAstronomicsToComponents(
                    $indonesiaComponentTargets,
                    $workbookTables['skema7'],
                );
                $skema7Index = null;
                foreach ($subPanels as $index => $panel) {
                    if (($panel['title'] ?? '') === 'Skema 7') {
                        $skema7Index = $index;
                        break;
                    }
                }
                if ($skema7Index !== null) {
                    $subPanels[$skema7Index]['columns'] = [
                        ['key' => 'label', 'label' => 'Baris'],
                        ['key' => 's0', 'label' => 'S0'],
                        ['key' => 'm2', 'label' => 'M2'],
                        ['key' => 's2', 'label' => 'S2'],
                        ['key' => 'n2', 'label' => 'N2'],
                        ['key' => 'k1', 'label' => 'K1'],
                        ['key' => 'o1', 'label' => 'O1'],
                        ['key' => 'm4', 'label' => 'M4'],
                        ['key' => 'ms4', 'label' => 'MS4'],
                        ['key' => 'k2', 'label' => 'K2'],
                        ['key' => 'p1', 'label' => 'P1'],
                    ];
                    $subPanels[$skema7Index]['rows'] = $workbookTables['skema7'];
                    $subPanels[$skema7Index]['items'] = [
                        'Skema 7 ini dibaca langsung dari workbook Excel, termasuk PR, f, V, u, r, A, dan g.',
                    ];
                }
            }
            if (is_array($workbookTables['forecasting'] ?? null)) {
                $forecastIndex = null;
                foreach ($subPanels as $index => $panel) {
                    if (($panel['title'] ?? '') === 'Forecasting Pasut') {
                        $forecastIndex = $index;
                        break;
                    }
                }
                if ($forecastIndex !== null) {
                    $subPanels[$forecastIndex]['columns'] = [
                        ['key' => 'no', 'label' => 'No'],
                        ['key' => 'date', 'label' => 'Tanggal'],
                        ['key' => 't', 'label' => 't (jam)'],
                        ['key' => 'm2', 'label' => 'M2'],
                        ['key' => 's2', 'label' => 'S2'],
                        ['key' => 'n2', 'label' => 'N2'],
                        ['key' => 'k1', 'label' => 'K1'],
                        ['key' => 'o1', 'label' => 'O1'],
                        ['key' => 'm4', 'label' => 'M4'],
                        ['key' => 'ms4', 'label' => 'MS4'],
                        ['key' => 'k2', 'label' => 'K2'],
                        ['key' => 'p1', 'label' => 'P1'],
                        ['key' => 'eta', 'label' => 'Eta(t)'],
                    ];
                    $subPanels[$forecastIndex]['rows'] = $workbookTables['forecasting'];
                    $subPanels[$forecastIndex]['items'] = [
                        'Cuplikan ini dibaca langsung dari sheet Forcasting Pasut workbook.',
                        'Tabel menampilkan langkah waktu, kontribusi komponen harmonik, dan hasil akhir eta(t).',
                    ];
                }
            }
            if ((defined('ENVIRONMENT') ? ENVIRONMENT : 'production') !== 'production' && is_array($workbookTables['skema7'] ?? null)) {
                $skema7RowsByLabel = [];
                foreach ($workbookTables['skema7'] as $row) {
                    $label = strtolower(trim((string) ($row['label'] ?? '')));
                    if ($label !== '') {
                        $skema7RowsByLabel[$label] = $row;
                    }
                }

                $findComponentValue = static function (string $label, string $key) use ($skema7RowsByLabel): string {
                    $row = $skema7RowsByLabel[strtolower($label)] ?? null;
                    if (! is_array($row)) {
                        return '-';
                    }

                    $value = $row[$key] ?? null;

                    return is_scalar($value) && trim((string) $value) !== '' ? (string) $value : '-';
                };

                $subPanels[] = [
                    'title' => 'Diagnostik M2 vs N2 (Dev)',
                    'description' => 'Panel audit sementara untuk memastikan nilai M2 dan N2 dibaca dari kolom workbook yang benar selama tahap pengembangan.',
                    'columns' => [
                        ['key' => 'item', 'label' => 'Item'],
                        ['key' => 'm2', 'label' => 'M2'],
                        ['key' => 'n2', 'label' => 'N2'],
                    ],
                    'rows' => [
                        ['item' => 'PR cos r', 'm2' => $findComponentValue('v: pr cos r', 'm2'), 'n2' => $findComponentValue('v: pr cos r', 'n2')],
                        ['item' => 'PR sin r', 'm2' => $findComponentValue('v: pr sin r', 'm2'), 'n2' => $findComponentValue('v: pr sin r', 'n2')],
                        ['item' => 'PR', 'm2' => $findComponentValue('pr', 'm2'), 'n2' => $findComponentValue('pr', 'n2')],
                        ['item' => 'P', 'm2' => $findComponentValue('tabel 3b : p', 'm2'), 'n2' => $findComponentValue('tabel 3b : p', 'n2')],
                        ['item' => 'A cm', 'm2' => $findComponentValue('a cm', 'm2'), 'n2' => $findComponentValue('a cm', 'n2')],
                        ['item' => 'go', 'm2' => $findComponentValue('go', 'm2'), 'n2' => $findComponentValue('go', 'n2')],
                    ],
                    'items' => [
                        'Panel ini hanya untuk pengembangan agar audit M2 dan N2 bisa dilakukan langsung dari UI.',
                        'Saat deploy, panel ini bisa dihilangkan tanpa memengaruhi hasil perhitungan.',
                    ],
                ];
            }
            $resolvedDatum = $this->resolvePredictionOffset($indonesiaComponentTargets, $msl);
            $templatePath = (string) (($workbookResult['engine_meta']['template_path'] ?? ''));
            $subPanels[] = [
                'title' => 'Konsistensi Workbook',
                'description' => 'Panel audit untuk membandingkan datum hasil workbook dengan statistik dasar observasi.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'MSL observasi', 'value' => number_format($msl, 4, '.', '')],
                    ['item' => 'S0 workbook', 'value' => number_format($resolvedDatum, 4, '.', '')],
                    ['item' => 'Selisih datum', 'value' => number_format($resolvedDatum - $msl, 4, '.', '')],
                    ['item' => 'Status', 'value' => abs($resolvedDatum - $msl) <= 0.0500 ? 'Selaras' : 'Perlu ditinjau'],
                ],
                'items' => [
                    'MSL observasi berasal dari rata-rata seluruh bacaan valid dataset.',
                    'S0 workbook berasal dari sheet skemA7 dan dipakai sebagai offset prediksi model Indonesia.',
                ],
            ];
            $subPanels[] = [
                'title' => 'Engine Workbook',
                'description' => 'Komponen harmonik untuk model Indonesia dibaca langsung dari workbook Excel Admiralty.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'Sumber', 'value' => 'Excel Workbook Admiralty'],
                    ['item' => 'Template', 'value' => $templatePath],
                ],
                'items' => [
                    'Perhitungan amplitudo dan fase model Indonesia sekarang tidak lagi memakai pendekatan Least Square.',
                    'Workbook template dijalankan ulang untuk setiap dataset yang dihitung.',
                ],
            ];
        } catch (RuntimeException $exception) {
            $subPanels[] = [
                'title' => 'Engine Workbook',
                'description' => 'Engine Excel Admiralty belum berhasil dijalankan.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'Status', 'value' => 'Fallback ke payload internal'],
                    ['item' => 'Pesan', 'value' => $exception->getMessage()],
                ],
                'items' => [
                    'Model Indonesia kembali memakai payload internal karena workbook tidak dapat diproses.',
                ],
            ];
        }

        return [
            $skemaIColumns,
            array_map(
                static function (array $row): array {
                    $result = [
                        'day_index' => $row['day_index'],
                        'date' => $row['date'],
                    ];
                    for ($hour = 0; $hour < 24; $hour++) {
                        $key = 'h' . str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
                        $result[$key] = $row[$key] ?? '0.0000';
                    }

                    return $result;
                },
                $dailyRows,
            ),
            [
                'Model aktif: Admiralty Hidro-Oseanografi Indonesia.',
                'Struktur hasil sekarang mengikuti workbook acuan: Skema 1, 2, 3, 4, 5&6, 7, lalu Forecasting Pasut.',
                'Skema 1 sekarang menampilkan matriks pengamatan harian 24 jam penuh seperti pola workbook.',
                'Skema 5&6 menampung kandidat besaran X/Y komponen, sedangkan Skema 7 merangkum besaran astronominya.',
                'Komponen harmonik model Indonesia kini diarahkan untuk dibaca dari workbook Excel Admiralty saat engine workbook tersedia.',
                'MSL tetap dihitung dari rata-rata seluruh elevasi pada dataset valid agar fondasi datum konsisten.',
            ],
            $indonesiaComponentTargets,
            'Skema 1',
            $subPanels,
        ];
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{0: list<array{key: string, label: string}>, 1: list<array<string, string>>, 2: list<string>, 3: list<array{name: string, group: string, status: string}>, 4: string, 5: list<array<string, mixed>>}
     */
    private function buildAdmiraltyKlasikPayload(array $prepared): array
    {
        $columns = [
            ['key' => 'no', 'label' => 'No'],
            ['key' => 'datetime', 'label' => 'Tanggal/Jam'],
            ['key' => 'time_hours', 'label' => 't (jam)'],
            ['key' => 'water_level', 'label' => 'Elevasi'],
            ['key' => 'deviation', 'label' => 'y - MSL'],
            ['key' => 'segment', 'label' => 'Segmen'],
        ];

        $rows = array_map(
            static fn (array $row): array => [
                'no'          => (string) $row['no'],
                'datetime'    => (string) $row['time_label'],
                'time_hours'  => number_format((float) $row['time_hours'], 2, '.', ''),
                'water_level' => number_format((float) $row['water_level'], 4, '.', ''),
                'deviation'   => number_format((float) $row['deviation'], 4, '.', ''),
                'segment'     => 'H-' . str_pad((string) $row['day_index'], 2, '0', STR_PAD_LEFT),
            ],
            array_slice($prepared, 0, 60),
        );

        return [
            $columns,
            $rows,
            [
                'Model aktif: Admiralty Klasik.',
                'Tabel kerja diarahkan ke format waktu kontinu t (jam) dari awal observasi.',
                'Kolom segmen disiapkan untuk membantu penelusuran blok waktu pada susunan tabel klasik.',
                'Tahap berikutnya adalah menurunkan komponen harmonik dengan skema tabel klasik 9 komponen.',
            ],
            $this->defaultComponentTargets('Siap dihitung dari skema klasik', 0.0),
            'Tabel Kerja Admiralty Klasik',
            [],
        ];
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{0: list<array{key: string, label: string}>, 1: list<array<string, string>>, 2: list<string>, 3: list<array{name: string, group: string, status: string}>, 4: string, 5: list<array<string, mixed>>}
     */
    private function buildLeastSquarePayload(array $prepared): array
    {
        $analysis = $this->runLeastSquareAnalysis($prepared);
        $columns = [
            ['key' => 'no', 'label' => 'No'],
            ['key' => 'datetime', 'label' => 'Tanggal/Jam'],
            ['key' => 'time_hours', 'label' => 't (jam)'],
            ['key' => 'water_level', 'label' => 'Observasi'],
            ['key' => 'deviation', 'label' => 'Centered y'],
            ['key' => 'm2_cos', 'label' => 'cos M2'],
            ['key' => 'm2_sin', 'label' => 'sin M2'],
        ];

        $rows = array_map(
            static fn (array $row): array => [
                'no'          => (string) $row['no'],
                'datetime'    => (string) $row['time_label'],
                'time_hours'  => number_format((float) $row['time_hours'], 2, '.', ''),
                'water_level' => number_format((float) $row['water_level'], 4, '.', ''),
                'deviation'   => number_format((float) $row['deviation'], 4, '.', ''),
                'm2_cos'      => number_format(cos((2 * M_PI / 12.4206012) * (float) $row['time_hours']), 4, '.', ''),
                'm2_sin'      => number_format(sin((2 * M_PI / 12.4206012) * (float) $row['time_hours']), 4, '.', ''),
            ],
            array_slice($prepared, 0, 60),
        );

        return [
            $columns,
            $rows,
            [
                'Model aktif: Least Square.',
                'Tabel kerja menekankan waktu kontinu t (jam) dan elevasi yang sudah dicenter terhadap MSL.',
                'Kolom cos M2 dan sin M2 adalah cuplikan basis; backend fitting semua 9 komponen sekaligus dengan persamaan normal least squares.',
                'Amplitudo dan fase di bawah ini sudah merupakan hasil estimasi numerik awal untuk 9 komponen.',
                'Nilai residual RMS saat ini: ' . number_format($analysis['residual_rms'], 4, '.', '') . ' meter.',
            ],
            $analysis['components'],
            'Tabel Kerja Least Square',
            [],
        ];
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{0: list<array{key: string, label: string}>, 1: list<array<string, string>>, 2: list<string>, 3: list<array{name: string, group: string, status: string}>, 4: string, 5: list<array<string, mixed>>}
     */
    private function buildAdmiraltyCatAPayload(array $prepared): array
    {
        $analysis = $this->runAdmiraltyCatAAnalysis($prepared);
        $columns = [
            ['key' => 'no', 'label' => 'No'],
            ['key' => 'datetime', 'label' => 'Tanggal/Jam'],
            ['key' => 'time_hours', 'label' => 't (jam)'],
            ['key' => 'water_level', 'label' => 'Observasi'],
            ['key' => 'deviation', 'label' => 'h - S0'],
            ['key' => 'm2_cos', 'label' => 'cos M2'],
            ['key' => 'm2_sin', 'label' => 'sin M2'],
            ['key' => 'k1_cos', 'label' => 'cos K1'],
            ['key' => 'k1_sin', 'label' => 'sin K1'],
        ];

        $rows = array_map(
            function (array $row): array {
                $timeHours = (float) $row['time_hours'];

                return [
                    'no'          => (string) $row['no'],
                    'datetime'    => (string) $row['time_label'],
                    'time_hours'  => number_format($timeHours, 2, '.', ''),
                    'water_level' => number_format((float) $row['water_level'], 4, '.', ''),
                    'deviation'   => number_format((float) $row['deviation'], 4, '.', ''),
                    'm2_cos'      => number_format(cos(deg2rad((360 / 12.4206012) * $timeHours)), 4, '.', ''),
                    'm2_sin'      => number_format(sin(deg2rad((360 / 12.4206012) * $timeHours)), 4, '.', ''),
                    'k1_cos'      => number_format(cos(deg2rad((360 / 23.9344721) * $timeHours)), 4, '.', ''),
                    'k1_sin'      => number_format(sin(deg2rad((360 / 23.9344721) * $timeHours)), 4, '.', ''),
                ];
            },
            array_slice($prepared, 0, 60),
        );

        return [
            $columns,
            $rows,
            [
                'Model aktif: Admiralty Cat A.',
                'Model ini mengambil inti rumus dari engine Admiralty PHP-native: S0 dihitung sebagai rata-rata observasi, lalu tiap komponen diekstrak dengan proyeksi cos/sin terhadap deret waktu.',
                'Prediksi dibangun memakai bentuk eta(t) = S0 + Σ(Ai * cos(wi*t - gi)) dengan waktu kontinu sejak awal dataset.',
                'Pendekatan ini sengaja dibuat dekat dengan data lapangan karena amplitudo dan fase langsung diturunkan dari observasi tanpa workbook Excel.',
                'Nilai residual RMS saat ini: ' . number_format($analysis['residual_rms'], 4, '.', '') . ' meter.',
            ],
            $analysis['components'],
            'Tabel Kerja Admiralty Cat A',
            [
                [
                    'title' => 'Ringkasan Ekstraksi Cat A',
                    'description' => 'Statistik singkat deret observasi dan hasil fitting Admiralty Cat A berbasis proyeksi harmonik sederhana.',
                    'columns' => [
                        ['key' => 'metric', 'label' => 'Metric'],
                        ['key' => 'value', 'label' => 'Nilai'],
                    ],
                    'rows' => array_merge(
                        $this->prefixSeriesSummaryRows($this->buildSeriesSummaryRows(array_map(static fn (array $row): float => (float) $row['water_level'], $prepared), 'Observasi'), 'Observasi'),
                        [
                            ['metric' => 'Residual RMS', 'value' => number_format($analysis['residual_rms'], 4, '.', '')],
                            ['metric' => 'Metode amplitudo', 'value' => 'A = (2/N) * sqrt(sumCos^2 + sumSin^2)'],
                            ['metric' => 'Metode fase', 'value' => 'g = atan2(sumSin, sumCos)'],
                        ],
                    ),
                    'items' => [
                        'Cat A memakai 9 komponen periodik yang sama dengan model lain agar perbandingan tetap adil.',
                        'Tidak ada workbook atau koreksi Excel; seluruh konstanta dihitung langsung dari observasi.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array{name: string, group: string, amplitude: string, phase: string, status: string}>
     */
    private function defaultComponentTargets(string $status, float $s0Amplitude): array
    {
        return array_map(
            static fn (array $component): array => [
                'name'      => $component['name'],
                'group'     => $component['group'],
                'period_hours' => $component['period_hours'],
                'amplitude' => $component['name'] === 'S0' ? number_format($s0Amplitude, 4, '.', '') : '-',
                'phase'     => $component['name'] === 'S0' ? '0.00' : '-',
                'status'    => $status,
            ],
            $this->componentDefinitions(),
        );
    }

    /**
     * @param list<float> $series
     * @return list<array{metric: string, value: string}>
     */
    private function buildSeriesSummaryRows(array $series, string $label): array
    {
        if ($series === []) {
            return [
                ['metric' => $label . ' tersedia', 'value' => 'Tidak'],
            ];
        }

        $count = count($series);
        $mean = array_sum($series) / $count;
        $absMean = array_sum(array_map(static fn (float $value): float => abs($value), $series)) / $count;
        $maxAbs = max(array_map(static fn (float $value): float => abs($value), $series));
        $variance = array_sum(array_map(static fn (float $value): float => ($value - $mean) ** 2, $series)) / $count;
        $stdDev = sqrt($variance);

        return [
            ['metric' => 'Jumlah nilai', 'value' => (string) $count],
            ['metric' => 'Rata-rata', 'value' => number_format($mean, 4, '.', '')],
            ['metric' => 'Rata-rata absolut', 'value' => number_format($absMean, 4, '.', '')],
            ['metric' => 'Simpangan baku', 'value' => number_format($stdDev, 4, '.', '')],
            ['metric' => 'Maksimum absolut', 'value' => number_format($maxAbs, 4, '.', '')],
        ];
    }

    /**
     * @param list<array{metric: string, value: string}> $rows
     * @return list<array{metric: string, value: string}>
     */
    private function prefixSeriesSummaryRows(array $rows, string $prefix): array
    {
        return array_map(
            static fn (array $row): array => [
                'metric' => $prefix . ' - ' . $row['metric'],
                'value' => $row['value'],
            ],
            $rows,
        );
    }

    /**
     * @param list<float> $series
     */
    private function seriesAbsMean(array $series): float
    {
        if ($series === []) {
            return 0.0;
        }

        return array_sum(array_map(static fn (float $value): float => abs($value), $series)) / count($series);
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{components: list<array{name: string, group: string, amplitude: string, phase: string, status: string}>, residual_rms: float}
     */
    private function runLeastSquareAnalysis(array $prepared): array
    {
        $definitions = array_values(array_filter(
            $this->componentDefinitions(),
            static fn (array $component): bool => (float) $component['period_hours'] > 0.0,
        ));
        $fit = $this->fitHarmonicLeastSquares(
            $this->buildSeriesRows($prepared),
            $definitions,
            'Dihitung dengan Least Square',
            'observed',
            true,
        );

        return [
            'components'   => $fit['components'],
            'residual_rms' => (float) ($fit['residual_rms'] ?? 0.0),
        ];
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{components: list<array{name: string, group: string, amplitude: string, phase: string, status: string}>, residual_rms: float}
     */
    private function runAdmiraltyCatAAnalysis(array $prepared): array
    {
        $seriesRows = $this->buildSeriesRows($prepared);
        if ($seriesRows === []) {
            throw new RuntimeException('Observasi tidak cukup untuk menghitung Admiralty Cat A.');
        }

        $levels = array_map(static fn (array $row): float => (float) ($row['observed'] ?? 0.0), $seriesRows);
        $sampleCount = count($levels);
        $s0 = array_sum($levels) / max($sampleCount, 1);
        $components = [];

        foreach ($this->componentDefinitions() as $definition) {
            $name = (string) $definition['name'];
            if ($name === 'S0') {
                $components[] = [
                    'name' => $name,
                    'group' => (string) $definition['group'],
                    'period_hours' => (float) $definition['period_hours'],
                    'amplitude' => number_format($s0, 4, '.', ''),
                    'phase' => '0.00',
                    'status' => 'Datum rata-rata observasi (S0) hasil Admiralty Cat A',
                ];
                continue;
            }

            $omegaDegreesPerHour = 360.0 / (float) $definition['period_hours'];
            $sumCos = 0.0;
            $sumSin = 0.0;

            foreach ($seriesRows as $row) {
                $timeHours = (float) ($row['time_hours'] ?? 0.0);
                $deviation = (float) ($row['observed'] ?? 0.0) - $s0;
                $argument = deg2rad($omegaDegreesPerHour * $timeHours);
                $sumCos += $deviation * cos($argument);
                $sumSin += $deviation * sin($argument);
            }

            $amplitude = (2 / $sampleCount) * sqrt(($sumCos * $sumCos) + ($sumSin * $sumSin));
            $phase = fmod(rad2deg(atan2($sumSin, $sumCos)) + 360.0, 360.0);

            $components[] = [
                'name' => $name,
                'group' => (string) $definition['group'],
                'period_hours' => (float) $definition['period_hours'],
                'amplitude' => number_format($amplitude, 4, '.', ''),
                'phase' => number_format($phase, 2, '.', ''),
                'status' => 'Diekstrak dengan proyeksi Fourier harmonik Admiralty Cat A',
            ];
        }

        $forecastRows = $this->forecastAdmiralty($seriesRows, $components, $s0);
        $residualRms = $this->computeRMSE(
            array_map(static fn (array $row): float => (float) ($row['observed'] ?? 0.0), $seriesRows),
            array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $forecastRows),
        );

        return [
            'components' => $components,
            'residual_rms' => $residualRms,
        ];
    }

    /**
     * @param list<list<float>> $matrix
     * @param list<float> $rhs
     * @return list<float>
     */
    private function solveLinearSystem(array $matrix, array $rhs): array
    {
        $size = count($matrix);

        for ($row = 0; $row < $size; $row++) {
            $pivotRow = $row;
            $pivotValue = abs($matrix[$row][$row]);

            for ($candidate = $row + 1; $candidate < $size; $candidate++) {
                $candidateValue = abs($matrix[$candidate][$row]);
                if ($candidateValue > $pivotValue) {
                    $pivotValue = $candidateValue;
                    $pivotRow = $candidate;
                }
            }

            if ($pivotValue < 1.0e-10) {
                throw new RuntimeException('Sistem least squares tidak stabil untuk dataset ini.');
            }

            if ($pivotRow !== $row) {
                [$matrix[$row], $matrix[$pivotRow]] = [$matrix[$pivotRow], $matrix[$row]];
                [$rhs[$row], $rhs[$pivotRow]] = [$rhs[$pivotRow], $rhs[$row]];
            }

            $pivot = $matrix[$row][$row];
            for ($column = $row; $column < $size; $column++) {
                $matrix[$row][$column] /= $pivot;
            }
            $rhs[$row] /= $pivot;

            for ($otherRow = 0; $otherRow < $size; $otherRow++) {
                if ($otherRow === $row) {
                    continue;
                }

                $factor = $matrix[$otherRow][$row];
                if (abs($factor) < 1.0e-12) {
                    continue;
                }

                for ($column = $row; $column < $size; $column++) {
                    $matrix[$otherRow][$column] -= $factor * $matrix[$row][$column];
                }
                $rhs[$otherRow] -= $factor * $rhs[$row];
            }
        }

        return array_map(static fn ($value): float => (float) $value, $rhs);
    }
}
