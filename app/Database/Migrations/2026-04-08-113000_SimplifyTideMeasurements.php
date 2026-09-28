<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SimplifyTideMeasurements extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldNames('tide_measurements');

        foreach ([
            'prediction_level',
            'source_endpoint',
            'raw_json',
            'created_at',
            'updated_at',
        ] as $column) {
            if (in_array($column, $fields, true)) {
                $this->forge->dropColumn('tide_measurements', $column);
            }
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('tide_measurements');

        if (! in_array('prediction_level', $fields, true)) {
            $this->forge->addColumn('tide_measurements', [
                'prediction_level' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,3',
                    'null'       => true,
                    'after'      => 'water_level',
                ],
            ]);
        }

        if (! in_array('source_endpoint', $fields, true)) {
            $this->forge->addColumn('tide_measurements', [
                'source_endpoint' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'prediction_level',
                ],
            ]);
        }

        if (! in_array('raw_json', $fields, true)) {
            $this->forge->addColumn('tide_measurements', [
                'raw_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                    'after' => 'source_endpoint',
                ],
            ]);
        }

        if (! in_array('created_at', $fields, true)) {
            $this->forge->addColumn('tide_measurements', [
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'raw_json',
                ],
            ]);
        }

        if (! in_array('updated_at', $fields, true)) {
            $this->forge->addColumn('tide_measurements', [
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'created_at',
                ],
            ]);
        }
    }
}
