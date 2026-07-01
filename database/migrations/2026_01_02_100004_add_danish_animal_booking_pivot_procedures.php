<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('booking');
        $sqlDir = database_path('sql/danish/animal_booking_pivot_procedures');

        $conn->unprepared(require "$sqlDir/sp_booking_attach_animals.php");
        $conn->unprepared(require "$sqlDir/sp_booking_detach_animals.php");
        $conn->unprepared(require "$sqlDir/sp_booking_get_animals.php");
        $conn->unprepared(require "$sqlDir/sp_booking_update_animal_remarks.php");
        $conn->unprepared(require "$sqlDir/sp_booking_get_animal_count.php");
    }

    public function down(): void
    {
        $conn = DB::connection('booking');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_attach_animals');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_detach_animals');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animals');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_animal_remarks');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animal_count');
    }
};
