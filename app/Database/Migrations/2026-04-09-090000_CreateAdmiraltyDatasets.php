<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmiraltyDatasets extends Migration
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
            'dataset_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'original_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'station_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'null'       => true,
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'null'       => true,
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'file_extension' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'interval_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'data_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'start_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'gap_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'duplicate_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'invalid_row_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'validation_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'validated',
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
        $this->forge->addKey('dataset_code', false, true, 'uniq_admiralty_dataset_code');
        $this->forge->createTable('admiralty_datasets', true);

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
            'row_number' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'observed_at' => [
                'type' => 'DATETIME',
            ],
            'water_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,4',
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
        $this->forge->addKey(['dataset_id', 'row_number'], false, true, 'uniq_admiralty_dataset_row');
        $this->forge->addKey(['dataset_id', 'observed_at'], false, true, 'uniq_admiralty_dataset_time');
        $this->forge->addKey('dataset_id', false, false, 'idx_admiralty_dataset_id');
        $this->forge->createTable('admiralty_dataset_observations', true);
    }

    public function down()
    {
        $this->forge->dropTable('admiralty_dataset_observations', true);
        $this->forge->dropTable('admiralty_datasets', true);
    }
}
