<?php

namespace App\Models;

use CodeIgniter\Model;

class AdmiraltyDatasetObservationModel extends Model
{
    protected $table            = 'admiralty_dataset_observations';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dataset_id',
        'row_number',
        'observed_at',
        'water_level',
    ];
    protected $useTimestamps = true;
}
