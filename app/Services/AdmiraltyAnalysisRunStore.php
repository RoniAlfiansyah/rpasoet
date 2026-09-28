<?php

namespace App\Services;

use App\Models\AdmiraltyAnalysisRunModel;
use RuntimeException;

class AdmiraltyAnalysisRunStore
{
    /**
     * @param array<string, mixed> $calculationResult
     * @return array<string, mixed>
     */
    public function store(int $datasetId, string $modelName, array $calculationResult): array
    {
        $summary = $calculationResult['summary'] ?? [];
        $model   = new AdmiraltyAnalysisRunModel();
        $runCode = 'RUN-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $runId = $model->insert([
            'dataset_id'   => $datasetId,
            'run_code'     => $runCode,
            'model_name'   => $modelName,
            'run_status'   => 'prepared',
            'msl'          => $summary['msl'] ?? null,
            'result_json'  => json_encode($calculationResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ], true);

        if (! is_numeric($runId)) {
            throw new RuntimeException('Analysis run gagal disimpan.');
        }

        return [
            'run_id'     => (int) $runId,
            'run_code'   => $runCode,
            'model_name' => $modelName,
            'run_status' => 'prepared',
        ];
    }
}
