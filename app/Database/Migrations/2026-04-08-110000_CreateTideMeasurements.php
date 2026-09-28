<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTideMeasurements extends Migration
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
            'station_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'measured_at_utc' => [
                'type' => 'DATETIME',
            ],
            'water_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'null'       => true,
            ],
            'prediction_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'null'       => true,
            ],
            'source_endpoint' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // LONGTEXT is used as a compatibility-friendly fallback where JSON support varies.
            'raw_json' => [
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
        $this->forge->addKey(['station_code', 'measured_at_utc'], false, true, 'uniq_station_time');
        $this->forge->addKey('station_code', false, false, 'idx_station_code');
        $this->forge->addKey('measured_at_utc', false, false, 'idx_measured_at');
        $this->forge->createTable('tide_measurements', true);
    }

    public function down()
    {
        $this->forge->dropTable('tide_measurements', true);
    }
}
