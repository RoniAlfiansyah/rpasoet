<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropExtraTideMeasurementColumns extends Migration
{
    public function up()
    {
        // This migration is intentionally left as a no-op because the same
        // column cleanup already happens in SimplifyTideMeasurements.
    }

    public function down()
    {
        // No-op to keep rollback symmetrical with the intentionally skipped up().
    }
}
