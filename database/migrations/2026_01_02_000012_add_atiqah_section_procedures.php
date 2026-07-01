<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('shelter');
        $sqlDir = database_path('sql/atiqah-shelter/section_procedures');

        $conn->unprepared(require "$sqlDir/sp_section_create.php");
        $conn->unprepared(require "$sqlDir/sp_section_read.php");
        $conn->unprepared(require "$sqlDir/sp_section_update.php");
        $conn->unprepared(require "$sqlDir/sp_section_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('shelter');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_section_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_section_read');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_section_update');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_section_delete');
    }
};
