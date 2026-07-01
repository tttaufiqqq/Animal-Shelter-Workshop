<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('booking');
        $sqlDir = database_path('sql/danish-booking/booking_procedures');

        $conn->unprepared(require "$sqlDir/sp_booking_create.php");
        $conn->unprepared(require "$sqlDir/sp_booking_read.php");
        $conn->unprepared(require "$sqlDir/sp_booking_update_status.php");
        $conn->unprepared(require "$sqlDir/sp_booking_cancel.php");
        $conn->unprepared(require "$sqlDir/sp_booking_check_time_conflicts.php");
    }

    public function down(): void
    {
        $conn = DB::connection('booking');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_read');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_status');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_cancel');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_booking_check_time_conflicts');
    }
};
