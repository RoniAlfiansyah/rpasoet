<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use Throwable;

class FetchTides extends BaseCommand
{
    protected $group = 'Data';

    protected $name = 'tides:fetch';

    protected $description = 'Fetches tide measurements from SRGI and upserts them into the database.';

    protected $usage = 'tides:fetch [--station=ULSU] [--date=2026-04-08]';

    protected $arguments = [];

    protected $options = [
        'station' => 'Optional station code. If omitted, all configured stations will be fetched.',
        'date'    => 'Optional date in YYYY-MM-DD format. If omitted, the realtime endpoint is used.',
    ];

    public function run(array $params)
    {
        $station = $this->resolveOption('station');
        $date    = $this->resolveOption('date');

        $stations = null;
        if (is_string($station) && trim($station) !== '') {
            $stations = [strtoupper(trim($station))];
        }

        CLI::write('Starting SRGI tide fetch...', 'yellow');

        try {
            $summary = Services::tideFetcher()->fetch($stations, is_string($date) ? $date : null);
        } catch (Throwable $e) {
            CLI::error($e->getMessage());

            return EXIT_ERROR;
        }

        foreach ($summary['results'] as $result) {
            $line = sprintf(
                '[%s] parsed=%d written=%d endpoint=%s',
                $result['station_code'],
                $result['rows_parsed'],
                $result['rows_written'],
                $result['endpoint'] ?? '-',
            );

            if ($result['success']) {
                CLI::write($line, 'green');
                continue;
            }

            CLI::write($line, 'red');
            CLI::write('  Error: ' . ($result['error'] ?? 'unknown error'), 'red');
        }

        CLI::newLine();
        CLI::write('Stations total     : ' . $summary['stations_total']);
        CLI::write('Stations succeeded : ' . $summary['stations_succeeded'], 'green');
        CLI::write('Stations failed    : ' . $summary['stations_failed'], $summary['stations_failed'] > 0 ? 'red' : 'yellow');
        CLI::write('Rows parsed        : ' . $summary['rows_parsed']);
        CLI::write('Rows written       : ' . $summary['rows_written']);

        return $summary['stations_failed'] > 0 ? EXIT_ERROR : EXIT_SUCCESS;
    }

    /**
     * @return string|true|null
     */
    private function resolveOption(string $name)
    {
        $value = CLI::getOption($name);
        if ($value !== null) {
            return $value;
        }

        foreach (CLI::getOptions() as $key => $optionValue) {
            if (str_starts_with($key, $name . '=')) {
                return substr($key, strlen($name) + 1);
            }
        }

        return null;
    }
}
