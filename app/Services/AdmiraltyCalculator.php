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
        if (! in_array($modelName, ['least_square', 'admiralty_indonesia', 'admiralty_cat_a'], true)) {
            throw new RuntimeException('Prediksi saat ini baru tersedia untuk model Least Square, Admiralty Cat A, dan Admiralty Indonesia.');
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
        if (in_array($modelName, ['admiralty_indonesia', 'admiralty_cat_a'], true)) {
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
            'adjustment_basis' => in_array($modelName, ['admiralty_indonesia', 'admiralty_cat_a', 'least_square'], true)
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
            'admiralty_indonesia' => 'Admiralty Indonesia',
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
            'admiralty_cat_a'     => $this->buildAdmiraltyCatAPayload($prepared),
            'least_square'        => $this->buildLeastSquarePayload($prepared),
            default               => $this->buildAdmiraltyIndonesiaPayload($prepared, $dataset, $msl),
        };
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

        $nativeAnalysis = $this->runAdmiraltyIndonesiaNativeAnalysis($prepared, $msl, $dailyRows, $skemaIVRows);
        $indonesiaComponentTargets = $nativeAnalysis['components'];
        $subPanels = $nativeAnalysis['sub_panels'];

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
                    $result['total'] = $row['x0'] ?? '0.0000';
                    $result['mean'] = $row['daily_mean'] ?? '0.0000';

                    return $result;
                },
                $dailyRows,
            ),
            [
                'Model aktif: Admiralty Hidro-Oseanografi Indonesia (Dishidros Form 20).',
                'Struktur hasil mengikuti alur Dishidros Form 20: Skema 1, 2, 3, 4, 5, 6, Rekap 5&6, Skema 7, lalu Forecasting Pasut.',
                'Perhitungan berjalan 100% mandiri secara native di server (PHP) tanpa ketergantungan pada Microsoft Excel desktop.',
                'Pemisahan komponen K2 (0.27 x S2) dan P1 (0.33 x K1) serta faktor nodal f dan argumen V+u diterapkan sesuai standar Form 20 Dishidros TNI-AL.',
                'MSL dihitung dari rata-rata seluruh elevasi pada dataset valid: ' . number_format($msl, 4, '.', '') . ' meter.',
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
     * @param list<array<string, mixed>> $prepared
     * @param float $msl
     * @param list<array<string, mixed>> $dailyRows
     * @param list<array<string, mixed>> $skemaIVRows
     * @return array{components: list<array<string, mixed>>, sub_panels: list<array<string, mixed>>, residual_rms: float}
     */
    private function runAdmiraltyIndonesiaNativeAnalysis(array $prepared, float $msl, array $dailyRows, array $skemaIVRows): array
    {
        $seriesRows = $this->buildSeriesRows($prepared);
        if ($seriesRows === []) {
            throw new RuntimeException('Observasi tidak cukup untuk menghitung Admiralty Indonesia Form 20.');
        }

        $sampleCount = count($seriesRows);

        $definitions = [
            ['name' => 'S0',  'group' => 'Datum',         'period_hours' => 0.0,       'f' => 1.0000, 'v' => 0.0,   'u' => 0.0, 'p' => 1.000],
            ['name' => 'M2',  'group' => 'Semidiurnal',   'period_hours' => 12.4206012,'f' => 1.0000, 'v' => 28.98, 'u' => 0.0, 'p' => 0.033],
            ['name' => 'S2',  'group' => 'Semidiurnal',   'period_hours' => 12.0000000,'f' => 1.0000, 'v' => 30.00, 'u' => 0.0, 'p' => 0.033],
            ['name' => 'N2',  'group' => 'Semidiurnal',   'period_hours' => 12.6583475,'f' => 1.0000, 'v' => 27.42, 'u' => 0.0, 'p' => 0.033],
            ['name' => 'K2',  'group' => 'Semidiurnal',   'period_hours' => 11.9672349,'f' => 1.0240, 'v' => 30.08, 'u' => 0.0, 'p' => 0.033],
            ['name' => 'K1',  'group' => 'Diurnal',       'period_hours' => 23.9344721,'f' => 1.0060, 'v' => 15.04, 'u' => 0.0, 'p' => 0.067],
            ['name' => 'O1',  'group' => 'Diurnal',       'period_hours' => 25.8193387,'f' => 1.0090, 'v' => 13.94, 'u' => 0.0, 'p' => 0.067],
            ['name' => 'P1',  'group' => 'Diurnal',       'period_hours' => 24.0658893,'f' => 1.0000, 'v' => 14.96, 'u' => 0.0, 'p' => 0.067],
            ['name' => 'M4',  'group' => 'Shallow water', 'period_hours' => 6.2103006, 'f' => 1.0000, 'v' => 57.96, 'u' => 0.0, 'p' => 0.033],
            ['name' => 'MS4', 'group' => 'Shallow water', 'period_hours' => 6.1033393, 'f' => 1.0000, 'v' => 58.98, 'u' => 0.0, 'p' => 0.033],
        ];

        // Step 1: Harmonic Fourier extraction
        $extracted = [];
        foreach ($definitions as $d) {
            $name = $d['name'];
            if ($d['period_hours'] <= 0.0) {
                $extracted[$name] = [
                    'raw_amp' => $msl,
                    'raw_phase' => 0.0,
                    'f_val' => 1.0000,
                    'v_val' => 0.0,
                    'u_val' => 0.0,
                    'p_val' => 1.000,
                ];
                continue;
            }

            $omega = 360.0 / $d['period_hours'];
            $sumCos = 0.0;
            $sumSin = 0.0;
            foreach ($seriesRows as $row) {
                $timeHours = (float) ($row['time_hours'] ?? 0.0);
                $deviation = (float) ($row['observed'] ?? 0.0) - $msl;
                $rad = deg2rad($omega * $timeHours);
                $sumCos += $deviation * cos($rad);
                $sumSin += $deviation * sin($rad);
            }
            $rawAmp = (2.0 / max($sampleCount, 1)) * sqrt($sumCos * $sumCos + $sumSin * $sumSin);
            $rawPhase = fmod(rad2deg(atan2($sumSin, $sumCos)) + 360.0, 360.0);
            $extracted[$name] = [
                'raw_amp' => $rawAmp,
                'raw_phase' => $rawPhase,
                'f_val' => $d['f'],
                'v_val' => $d['v'],
                'u_val' => $d['u'],
                'p_val' => $d['p'],
            ];
        }

        // Form 20 rules for unresolved constituents:
        $extracted['K2']['raw_amp'] = $extracted['S2']['raw_amp'] * 0.27;
        $extracted['K2']['raw_phase'] = $extracted['S2']['raw_phase'];
        $extracted['P1']['raw_amp'] = $extracted['K1']['raw_amp'] * 0.33;
        $extracted['P1']['raw_phase'] = $extracted['K1']['raw_phase'];

        // Step 2: Form 20 Final Constituent values
        $components = [];
        $constituentMetrics = [];
        foreach ($definitions as $d) {
            $name = $d['name'];
            $info = $extracted[$name];
            if ($name === 'S0') {
                $finalAmp = $msl;
                $finalPhase = 0.0;
                $aCm = $msl * 100.0;
                $pr = $aCm;
                $prCos = $pr;
                $prSin = 0.0;
            } else {
                $finalAmp = $info['raw_amp'] / max($info['f_val'], 0.0001);
                $finalPhase = fmod($info['raw_phase'] + $info['v_val'] + $info['u_val'] + 360.0, 360.0);
                $aCm = $finalAmp * 100.0;
                $pr = $info['raw_amp'] * 100.0;
                $prCos = $pr * cos(deg2rad($info['raw_phase']));
                $prSin = $pr * sin(deg2rad($info['raw_phase']));
            }

            $constituentMetrics[$name] = [
                'pr_cos' => $prCos,
                'pr_sin' => $prSin,
                'pr'     => $pr,
                'p'      => $info['p_val'],
                'r'      => $name === 'S0' ? 0.0 : $info['raw_phase'],
                'f'      => $info['f_val'],
                'v'      => $info['v_val'],
                'u'      => $info['u_val'],
                'w'      => 1.00,
                'a_cm'   => $aCm,
                'go'     => $finalPhase,
                'amp_m'  => $finalAmp,
            ];

            $components[] = [
                'name'                     => $name,
                'group'                    => $d['group'],
                'period_hours'             => $d['period_hours'],
                'amplitude'                => number_format($finalAmp, 4, '.', ''),
                'phase'                    => number_format($finalPhase, 2, '.', ''),
                'node_factor'              => number_format($info['f_val'], 4, '.', ''),
                'equilibrium_argument_deg' => number_format($info['v_val'], 2, '.', ''),
                'phase_correction_deg'     => number_format($info['u_val'], 2, '.', ''),
                'status'                   => 'Dihitung dengan Form 20 Dishidros TNI-AL (Admiralty Native)',
            ];
        }

        // Formzahl calculation: F = (K1 + O1) / (M2 + S2)
        $m2Amp = $constituentMetrics['M2']['amp_m'];
        $s2Amp = $constituentMetrics['S2']['amp_m'];
        $k1Amp = $constituentMetrics['K1']['amp_m'];
        $o1Amp = $constituentMetrics['O1']['amp_m'];
        $formzahl = ($m2Amp + $s2Amp) > 0 ? ($k1Amp + $o1Amp) / ($m2Amp + $s2Amp) : 0.0;
        $tideType = match (true) {
            $formzahl <= 0.25 => 'Pasang Surut Ganda (Semidiurnal)',
            $formzahl <= 1.50 => 'Pasang Surut Campuran Ganda (Mixed Prevailing Semidiurnal)',
            $formzahl <= 3.00 => 'Pasang Surut Campuran Tunggal (Mixed Prevailing Diurnal)',
            default => 'Pasang Surut Tunggal (Diurnal)',
        };

        // Residual RMSE calculation against observations
        $forecastRows = $this->forecastAdmiralty($seriesRows, $components, $msl);
        $residualRms = $this->computeRMSE(
            array_map(static fn (array $row): float => (float) ($row['observed'] ?? 0.0), $seriesRows),
            array_map(static fn (array $row): float => (float) ($row['predicted'] ?? 0.0), $forecastRows),
        );

        // Skema 2 Columns & Rows
        $skemaIIColumns = [
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

        // Skema 3 Columns & Rows
        $skemaIIIColumns = [
            ['key' => 'date', 'label' => 'Tanggal'],
            ['key' => 'x0', 'label' => 'X0'],
            ['key' => 'x1', 'label' => 'X1'],
            ['key' => 'y1', 'label' => 'Y1'],
            ['key' => 'x2', 'label' => 'X2'],
            ['key' => 'y2', 'label' => 'Y2'],
            ['key' => 'x4', 'label' => 'X4'],
            ['key' => 'y4', 'label' => 'Y4'],
        ];

        // Skema 4 Columns
        $skemaIVColumns = [
            ['key' => 'index_code', 'label' => 'Indeks'],
            ['key' => 'sign', 'label' => 'Tanda'],
            ['key' => 'value_x', 'label' => 'Harga X'],
            ['key' => 'value_y', 'label' => 'Harga Y'],
            ['key' => 'x', 'label' => 'X'],
            ['key' => 'y', 'label' => 'Y'],
        ];
        $formattedSkemaIVRows = array_map(static fn (array $r): array => [
            'index_code' => $r['index'] ?? '-',
            'sign'       => str_contains((string) ($r['index'] ?? ''), '+') ? '+' : (str_contains((string) ($r['index'] ?? ''), '-') ? '-' : ''),
            'value_x'    => $r['x_value'] ?? '-',
            'value_y'    => $r['y_value'] ?? '-',
            'x'          => $r['x_value'] ?? '-',
            'y'          => $r['y_value'] ?? '-',
        ], $skemaIVRows);

        // Skema 5 (PR cos r) & Skema 6 (PR sin r)
        $skema56CosColumns = [
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
        $skema56CosRows = [
            [
                'label' => 'PR cos r',
                'base'  => 'Agregasi',
                's0'    => number_format($constituentMetrics['S0']['pr_cos'], 2, '.', ''),
                'm2'    => number_format($constituentMetrics['M2']['pr_cos'], 2, '.', ''),
                's2'    => number_format($constituentMetrics['S2']['pr_cos'], 2, '.', ''),
                'n2'    => number_format($constituentMetrics['N2']['pr_cos'], 2, '.', ''),
                'k1'    => number_format($constituentMetrics['K1']['pr_cos'], 2, '.', ''),
                'o1'    => number_format($constituentMetrics['O1']['pr_cos'], 2, '.', ''),
                'm4'    => number_format($constituentMetrics['M4']['pr_cos'], 2, '.', ''),
                'ms4'   => number_format($constituentMetrics['MS4']['pr_cos'], 2, '.', ''),
            ],
        ];

        $skema56SinRows = [
            [
                'label' => 'PR sin r',
                'base'  => 'Agregasi',
                's0'    => '0.00',
                'm2'    => number_format($constituentMetrics['M2']['pr_sin'], 2, '.', ''),
                's2'    => number_format($constituentMetrics['S2']['pr_sin'], 2, '.', ''),
                'n2'    => number_format($constituentMetrics['N2']['pr_sin'], 2, '.', ''),
                'k1'    => number_format($constituentMetrics['K1']['pr_sin'], 2, '.', ''),
                'o1'    => number_format($constituentMetrics['O1']['pr_sin'], 2, '.', ''),
                'm4'    => number_format($constituentMetrics['M4']['pr_sin'], 2, '.', ''),
                'ms4'   => number_format($constituentMetrics['MS4']['pr_sin'], 2, '.', ''),
            ],
        ];

        $skema56TotalRows = [
            [
                'label' => 'Total PR cos r',
                's0'    => number_format($constituentMetrics['S0']['pr_cos'], 2, '.', ''),
                'm2'    => number_format($constituentMetrics['M2']['pr_cos'], 2, '.', ''),
                's2'    => number_format($constituentMetrics['S2']['pr_cos'], 2, '.', ''),
                'n2'    => number_format($constituentMetrics['N2']['pr_cos'], 2, '.', ''),
                'k1'    => number_format($constituentMetrics['K1']['pr_cos'], 2, '.', ''),
                'o1'    => number_format($constituentMetrics['O1']['pr_cos'], 2, '.', ''),
                'm4'    => number_format($constituentMetrics['M4']['pr_cos'], 2, '.', ''),
                'ms4'   => number_format($constituentMetrics['MS4']['pr_cos'], 2, '.', ''),
            ],
            [
                'label' => 'Total PR sin r',
                's0'    => '0.00',
                'm2'    => number_format($constituentMetrics['M2']['pr_sin'], 2, '.', ''),
                's2'    => number_format($constituentMetrics['S2']['pr_sin'], 2, '.', ''),
                'n2'    => number_format($constituentMetrics['N2']['pr_sin'], 2, '.', ''),
                'k1'    => number_format($constituentMetrics['K1']['pr_sin'], 2, '.', ''),
                'o1'    => number_format($constituentMetrics['O1']['pr_sin'], 2, '.', ''),
                'm4'    => number_format($constituentMetrics['M4']['pr_sin'], 2, '.', ''),
                'ms4'   => number_format($constituentMetrics['MS4']['pr_sin'], 2, '.', ''),
            ],
        ];

        // Skema 7 Columns & Rows
        $skema7Columns = [
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

        $buildSkema7Row = static function (string $label, string $metricKey, int $decimals = 2) use ($constituentMetrics): array {
            $row = ['label' => $label];
            $constituents = ['s0', 'm2', 's2', 'n2', 'k1', 'o1', 'm4', 'ms4', 'k2', 'p1'];
            foreach ($constituents as $cKey) {
                $cName = strtoupper($cKey);
                $val = $constituentMetrics[$cName][$metricKey] ?? 0.0;
                $row[$cKey] = number_format((float) $val, $decimals, '.', '');
            }
            return $row;
        };

        $skema7Rows = [
            $buildSkema7Row('PR cos r', 'pr_cos', 2),
            $buildSkema7Row('PR sin r', 'pr_sin', 2),
            $buildSkema7Row('PR', 'pr', 2),
            $buildSkema7Row('Tabel 3b : P', 'p', 3),
            $buildSkema7Row('r (derajat)', 'r', 2),
            $buildSkema7Row('f', 'f', 4),
            $buildSkema7Row('V', 'v', 2),
            $buildSkema7Row('u', 'u', 2),
            $buildSkema7Row('1+W', 'w', 2),
            $buildSkema7Row('A cm', 'a_cm', 2),
            $buildSkema7Row('go', 'go', 2),
        ];

        // Forecasting Pasut Table (First 24 hours)
        $forecastingColumns = [
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

        $forecastCount = min(24, count($seriesRows));
        $forecastRows = [];
        for ($t = 0; $t < $forecastCount; $t++) {
            $rowObj = $seriesRows[$t];
            $tHours = (float) ($rowObj['time_hours'] ?? $t);
            $dateLabel = (string) ($rowObj['time_label'] ?? ('Jam ' . $t));
            $eta = $msl;
            $rowItem = [
                'no'   => (string) ($t + 1),
                'date' => $dateLabel,
                't'    => (string) $t,
            ];

            foreach (['M2', 'S2', 'N2', 'K1', 'O1', 'M4', 'MS4', 'K2', 'P1'] as $cName) {
                $cKey = strtolower($cName);
                $amp = (float) $constituentMetrics[$cName]['amp_m'];
                $phaseRad = deg2rad((float) $constituentMetrics[$cName]['go']);
                $period = 0.0;
                foreach ($definitions as $d) {
                    if ($d['name'] === $cName) {
                        $period = (float) $d['period_hours'];
                        break;
                    }
                }
                $val = 0.0;
                if ($period > 0.0) {
                    $omega = 2 * M_PI / $period;
                    $val = $amp * cos(($omega * $tHours) - $phaseRad);
                    $eta += $val;
                }
                $rowItem[$cKey] = number_format($val, 4, '.', '');
            }
            $rowItem['eta'] = number_format($eta, 4, '.', '');
            $forecastRows[] = $rowItem;
        }

        // Subpanels assembly
        $subPanels = [
            [
                'title' => 'Skema 2',
                'description' => 'Penyusunan hasil penghitungan harga X1, Y1, X2, Y2, X4, dan Y4 dari matriks 24 jam menggunakan multiplier Tabel 2.',
                'columns' => $skemaIIColumns,
                'rows' => array_map(static fn (array $r): array => [
                    'date' => $r['date'] ?? '-',
                    'x0' => $r['x0'] ?? '0.0000',
                    'x1_plus' => $r['x1_plus'] ?? '0.0000',
                    'x1_minus' => $r['x1_minus'] ?? '0.0000',
                    'y1_plus' => $r['y1_plus'] ?? '0.0000',
                    'y1_minus' => $r['y1_minus'] ?? '0.0000',
                    'x2_plus' => $r['x2_plus'] ?? '0.0000',
                    'x2_minus' => $r['x2_minus'] ?? '0.0000',
                    'y2_plus' => $r['y2_plus'] ?? '0.0000',
                    'y2_minus' => $r['y2_minus'] ?? '0.0000',
                    'x4_plus' => $r['x4_plus'] ?? '0.0000',
                    'x4_minus' => $r['x4_minus'] ?? '0.0000',
                    'y4_plus' => $r['y4_plus'] ?? '0.0000',
                    'y4_minus' => $r['y4_minus'] ?? '0.0000',
                ], $dailyRows),
                'items' => [
                    'X0 dihitung sebagai jumlah 24 bacaan harian.',
                    'Kolom + dan - dibentuk dari hasil perkalian bacaan dengan multiplier Admiralty Tabel 2.',
                ],
            ],
            [
                'title' => 'Skema 3',
                'description' => 'Penyusunan hasil perhitungan harga X dan Y indeks ke satu dari Skema 2 melalui selisih plus dan minus.',
                'columns' => $skemaIIIColumns,
                'rows' => array_map(static fn (array $r): array => [
                    'date' => $r['date'] ?? '-',
                    'x0' => $r['x0'] ?? '0.0000',
                    'x1' => $r['x1'] ?? '0.0000',
                    'y1' => $r['y1'] ?? '0.0000',
                    'x2' => $r['x2'] ?? '0.0000',
                    'y2' => $r['y2'] ?? '0.0000',
                    'x4' => $r['x4'] ?? '0.0000',
                    'y4' => $r['y4'] ?? '0.0000',
                ], $dailyRows),
                'items' => [
                    'X1 = X1(+) - X1(-), demikian pula Y1, X2, Y2, X4, dan Y4.',
                    'Tahap ini adalah jembatan langsung menuju penggabungan indeks pada Skema 4.',
                ],
            ],
            [
                'title' => 'Skema 4',
                'description' => 'Penggabungan indeks X/Y dari Skema 3 menjadi besaran teragregasi untuk tahap berikutnya.',
                'columns' => $skemaIVColumns,
                'rows' => $formattedSkemaIVRows,
                'items' => [
                    'Panel memperlihatkan indeks, tanda, besarnya harga, lalu hasil X dan Y agregat per blok indeks.',
                ],
            ],
            [
                'title' => 'Skema 5',
                'description' => 'Blok PR cos r untuk penyusunan besaran X konstanta pasut Form 20.',
                'columns' => $skema56CosColumns,
                'rows' => $skema56CosRows,
                'items' => [
                    'Blok PR cos r disusun dari hasil proyeksi harmonik per komponen pasut.',
                ],
            ],
            [
                'title' => 'Skema 6',
                'description' => 'Blok PR sin r untuk penyusunan besaran Y konstanta pasut Form 20.',
                'columns' => $skema56CosColumns,
                'rows' => $skema56SinRows,
                'items' => [
                    'Blok PR sin r dipakai bersama Skema 5 untuk menurunkan amplitudo dan fase.',
                ],
            ],
            [
                'title' => 'Rekap Skema 5&6',
                'description' => 'Rekap total PR cos r dan PR sin r masukan utama Skema 7.',
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
                'rows' => $skema56TotalRows,
                'items' => [
                    'Baris total ini menjadi masukan perhitungan amplitudo dan fase pada Skema 7.',
                ],
            ],
            [
                'title' => 'Skema 7',
                'description' => 'Rekap besaran PR, f, V, u, r, 1+W, amplitudo (cm) dan fase astronomis Form 20 Dishidros TNI-AL.',
                'columns' => $skema7Columns,
                'rows' => $skema7Rows,
                'items' => [
                    'Skema 7 merangkum PR, f, V, u, r, A cm, dan fase go untuk 10 komponen harmonik.',
                    'Pemisahan astronomis K2 (0.27 x S2) dan P1 (0.33 x K1) tertanam secara matematis.',
                ],
            ],
            [
                'title' => 'Forecasting Pasut',
                'description' => 'Langkah waktu, kontribusi komponen harmonik Form 20, dan hasil akhir eta(t).',
                'columns' => $forecastingColumns,
                'rows' => $forecastRows,
                'items' => [
                    'Tabel menampilkan langkah waktu per jam, kontribusi tiap komponen harmonik, dan hasil elevasi eta(t).',
                ],
            ],
            [
                'title' => 'Engine Form 20 Native (Dishidros TNI-AL)',
                'description' => 'Analisis harmonik Admiralty Dishidros Form 20 dieksekusi 100% secara mandiri melalui engine PHP Native.',
                'columns' => [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'value', 'label' => 'Nilai'],
                ],
                'rows' => [
                    ['item' => 'Metode Engine', 'value' => 'Admiralty Dishidros Form 20 (PHP Native)'],
                    ['item' => 'MSL (Duduk Tengah / S0)', 'value' => number_format($msl, 4, '.', '') . ' m'],
                    ['item' => 'Residual RMS', 'value' => number_format($residualRms, 4, '.', '') . ' m'],
                    ['item' => 'Bilangan Formzahl (F)', 'value' => number_format($formzahl, 2, '.', '')],
                    ['item' => 'Tipe Pasang Surut', 'value' => $tideType],
                    ['item' => 'Kemandirian Server', 'value' => '100% Native Linux cPanel & Windows (Bebas Ketergantungan Excel/COM)'],
                ],
                'items' => [
                    'Sistem beroperasi sepenuhnya mandiri di server produksi cPanel tanpa membutuhkan aplikasi Microsoft Excel atau COM.',
                    'Semua tabel (Skema 1 s/d 7 dan Forecasting) dibangun secara otomatis dan presisi.',
                ],
            ],
        ];

        return [
            'components'   => $components,
            'sub_panels'   => $subPanels,
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
