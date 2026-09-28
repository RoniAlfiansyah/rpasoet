<?php

namespace App\Models;

use CodeIgniter\Model;

class AdmiraltyDatasetModel extends Model
{
    protected $table            = 'admiralty_datasets';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dataset_code',
        'original_filename',
        'station_name',
        'latitude',
        'longitude',
        'timezone',
        'file_extension',
        'interval_minutes',
        'data_count',
        'start_at',
        'end_at',
        'gap_count',
        'duplicate_count',
        'invalid_row_count',
        'validation_status',
    ];
    protected $useTimestamps = true;
}
