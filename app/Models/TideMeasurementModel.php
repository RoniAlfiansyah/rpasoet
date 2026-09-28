<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

class TideMeasurementModel extends Model
{
    protected $table            = 'tide_measurements';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'station_code',
        'measured_at_utc',
        'water_level',
    ];

    protected $useTimestamps = false;

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function upsertMeasurements(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        $db = $this->db;
        $db->transStart();

        $result = $this->builder()
            ->onConstraint('station_code, measured_at_utc')
            ->updateFields('water_level')
            ->upsertBatch($rows, null, 100);

        $db->transComplete();

        if (! $db->transStatus()) {
            throw new RuntimeException('Failed to upsert tide measurements.');
        }

        return count($rows);
    }
}
