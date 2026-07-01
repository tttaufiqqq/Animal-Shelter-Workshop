<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('shelter');
        $sqlDir = database_path('sql/atiqah/category_procedures');

        $conn->unprepared(require "$sqlDir/sp_category_create.php");
        $conn->unprepared(require "$sqlDir/sp_category_read.php");
        $conn->unprepared(require "$sqlDir/sp_category_update.php");
        $conn->unprepared(require "$sqlDir/sp_category_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('shelter');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_category_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_category_read');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_category_update');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_category_delete');
    }
};
