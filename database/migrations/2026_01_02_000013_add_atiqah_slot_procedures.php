<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('shelter');
        $sqlDir = database_path('sql/atiqah-shelter/slot_procedures');

        $conn->unprepared(require "$sqlDir/sp_slot_create.php");
        $conn->unprepared(require "$sqlDir/sp_slot_read.php");
        $conn->unprepared(require "$sqlDir/sp_slot_update.php");
        $conn->unprepared(require "$sqlDir/sp_slot_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('shelter');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_slot_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_slot_read');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_slot_update');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_slot_delete');
    }
};
