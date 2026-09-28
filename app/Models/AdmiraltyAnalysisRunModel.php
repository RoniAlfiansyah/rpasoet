<?php

namespace App\Models;

use CodeIgniter\Model;

class AdmiraltyAnalysisRunModel extends Model
{
    protected $table            = 'admiralty_analysis_runs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dataset_id',
        'run_code',
        'model_name',
        'run_status',
        'msl',
        'result_json',
    ];
    protected $useTimestamps = true;
}
