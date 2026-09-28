<?php

namespace App\Services;

use RuntimeException;

class AdmiraltyExcelEngine
{
    private string $engineVariant;
    private string $templatePath;
    private string $scriptPath;

    public function __construct(string $engineVariant = 'indonesia', ?string $templatePath = null)
    {
        $this->engineVariant = $engineVariant;
        $this->templatePath = $this->normalizePath($templatePath ?? $this->resolveTemplatePath($engineVariant));
        $this->scriptPath = $this->resolveScriptPath($engineVariant);
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @param list<array{name: string, group: string, period_hours: float}> $definitions
     * @return array<string, mixed>
     */
    public function calculate(array $dataset, array $prepared, array $definitions): array
    {
        if (! is_file($this->templatePath)) {
            throw new RuntimeException('Template workbook Admiralty tidak ditemukan di: ' . $this->templatePath);
        }

        $workspace = WRITEPATH . 'admiralty-excel';
        if (! is_dir($workspace) && ! mkdir($workspace, 0777, true) && ! is_dir($workspace)) {
            throw new RuntimeException('Folder kerja Admiralty Excel tidak dapat dibuat.');
        }

        $jobId = 'excel-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        $inputPath = $workspace . DIRECTORY_SEPARATOR . $jobId . '-input.json';
        $outputPath = $workspace . DIRECTORY_SEPARATOR . $jobId . '-output.json';
        if (! is_file($this->scriptPath)) {
            throw new RuntimeException('Script engine Admiralty Excel tidak ditemukan.');
        }

        $rows = array_map(
            static fn (array $row): array => [
                'datetime' => (string) ($row['time_label'] ?? ''),
                'water_level' => isset($row['water_level']) ? (float) $row['water_level'] : 0.0,
            ],
            $prepared,
        );

        $payload = [
            'template_path' => $this->templatePath,
            'station_name' => (string) ($dataset['station_name'] ?? ''),
            'timezone' => (string) ($dataset['timezone'] ?? 'Asia/Jakarta'),
            'rows' => $rows,
        ];

        file_put_contents($inputPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $command = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File '
            . $this->escapeWindowsArg($this->scriptPath)
            . ' -TemplatePath ' . $this->escapeWindowsArg($this->templatePath)
            . ' -InputPath ' . $this->escapeWindowsArg($inputPath)
            . ' -OutputPath ' . $this->escapeWindowsArg($outputPath);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, ROOTPATH);
        if (! is_resource($process)) {
            throw new RuntimeException('Engine Excel Admiralty tidak dapat dijalankan.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException('Engine Excel Admiralty gagal: ' . trim($stderr !== '' ? $stderr : $stdout));
        }

        if (! is_file($outputPath)) {
            throw new RuntimeException('Output engine Excel Admiralty tidak ditemukan.');
        }

        $rawOutput = (string) file_get_contents($outputPath);
        $rawOutput = preg_replace('/^\xEF\xBB\xBF/', '', $rawOutput) ?? $rawOutput;
        $result = json_decode($rawOutput, true);
        if (! is_array($result) || ! is_array($result['components'] ?? null)) {
            throw new RuntimeException('Output engine Excel Admiralty tidak valid.');
        }

        $definitionMap = [];
        foreach ($definitions as $definition) {
            $definitionMap[$definition['name']] = $definition;
        }

        $components = [];
        foreach ($result['components'] as $component) {
            $name = (string) ($component['name'] ?? '');
            if ($name === '' || ! isset($definitionMap[$name])) {
                continue;
            }

            $amplitude = (float) ($component['amplitude'] ?? 0.0);

            $components[] = [
                'name' => $name,
                'group' => $definitionMap[$name]['group'],
                'period_hours' => $definitionMap[$name]['period_hours'],
                'amplitude' => number_format($amplitude / 100, 4, '.', ''),
                'phase' => number_format((float) ($component['phase'] ?? 0.0), 2, '.', ''),
                'status' => 'Dihitung dari workbook Excel Admiralty',
            ];
        }

        return [
            'components' => $components,
            'engine_meta' => [
                'template_path' => $this->templatePath,
                'workbook_copy' => (string) ($result['workbook_copy'] ?? ''),
                'source' => 'Excel Workbook Admiralty',
                'engine_variant' => $this->engineVariant,
            ],
            'tables' => is_array($result['tables'] ?? null) ? $result['tables'] : [],
        ];
    }

    private function resolveTemplatePath(string $engineVariant): string
    {
        return match ($engineVariant) {
            'hidros' => $this->resolveConfiguredPath(
                getenv('ADMIRALTY_EXCEL_TEMPLATE_HIDROS'),
                ROOTPATH . 'engine' . DIRECTORY_SEPARATOR . 'Admiralty_Hidros_Engine_Base.xls'
            ),
            default => $this->resolveConfiguredPath(
                getenv('ADMIRALTY_EXCEL_TEMPLATE_INDONESIA') ?: getenv('ADMIRALTY_EXCEL_TEMPLATE'),
                ROOTPATH . 'engine' . DIRECTORY_SEPARATOR . 'Admiralty_HOI_Engine.xlsx'
            ),
        };
    }

    private function resolveScriptPath(string $engineVariant): string
    {
        return match ($engineVariant) {
            'hidros' => ROOTPATH . 'tools' . DIRECTORY_SEPARATOR . 'admiralty_excel_hidros_engine.ps1',
            default => ROOTPATH . 'tools' . DIRECTORY_SEPARATOR . 'admiralty_excel_engine.ps1',
        };
    }

    private function resolveConfiguredPath($configured, string $defaultPath): string
    {
        if (! is_string($configured) || trim($configured) === '') {
            return $defaultPath;
        }

        return $this->normalizePath($configured);
    }

    private function normalizePath(string $path): string
    {
        return trim(trim($path), "\"'");
    }

    private function escapeWindowsArg(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }
}
