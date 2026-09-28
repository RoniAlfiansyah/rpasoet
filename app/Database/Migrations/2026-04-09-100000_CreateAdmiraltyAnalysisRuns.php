<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmiraltyAnalysisRuns extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'dataset_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'run_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'model_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'run_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'prepared',
            ],
            'msl' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,4',
                'null'       => true,
            ],
            'result_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('run_code', false, true, 'uniq_admiralty_run_code');
        $this->forge->addKey(['dataset_id', 'model_name'], false, false, 'idx_admiralty_dataset_model');
        $this->forge->createTable('admiralty_analysis_runs', true);
    }

    public function down()
    {
        $this->forge->dropTable('admiralty_analysis_runs', true);
    }
}
