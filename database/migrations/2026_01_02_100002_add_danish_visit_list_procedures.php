<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('booking');
        $sqlDir = database_path('sql/danish-booking/visit_list_procedures');

        $conn->unprepared(require "$sqlDir/sp_visit_list_get_or_create.php");
        $conn->unprepared(require "$sqlDir/sp_visit_list_add_animal.php");
        $conn->unprepared(require "$sqlDir/sp_visit_list_remove_animal.php");
        $conn->unprepared(require "$sqlDir/sp_visit_list_get_animals.php");
        $conn->unprepared(require "$sqlDir/sp_visit_list_delete.php");
        $conn->unprepared(require "$sqlDir/sp_visit_list_clear_animals.php");
    }

    public function down(): void
    {
        $conn = DB::connection('booking');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_or_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_add_animal');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_remove_animal');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_animals');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_delete');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_clear_animals');
    }
};
