<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('shelter');
        $sqlDir = database_path('sql/atiqah/inventory_procedures');

        $conn->unprepared(require "$sqlDir/sp_inventory_create.php");
        $conn->unprepared(require "$sqlDir/sp_inventory_read.php");
        $conn->unprepared(require "$sqlDir/sp_inventory_update.php");
        $conn->unprepared(require "$sqlDir/sp_inventory_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('shelter');

        foreach (['sp_inventory_create', 'sp_inventory_read',
                  'sp_inventory_update', 'sp_inventory_delete'] as $proc) {
            $conn->unprepared("DROP PROCEDURE IF EXISTS {$proc}");
        }
    }
};
