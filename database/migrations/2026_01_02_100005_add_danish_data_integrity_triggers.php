<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('booking');
        $sqlDir = database_path('sql/danish/data_integrity_triggers');

        $conn->unprepared(require "$sqlDir/trg_booking.php");
        $conn->unprepared(require "$sqlDir/trg_visit_list.php");
        $conn->unprepared(require "$sqlDir/trg_adoption.php");
        $conn->unprepared(require "$sqlDir/trg_transaction.php");
    }

    public function down(): void
    {
        $conn = DB::connection('booking');

        $conn->unprepared('DROP TRIGGER IF EXISTS trg_booking_cascade_delete');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_cascade_delete');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_booking_prevent_delete_with_adoptions');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_transaction_prevent_delete_with_adoptions');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_booking_update_timestamp');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_update_timestamp');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_adoption_update_timestamp');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_transaction_update_timestamp');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_status_transition');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_insert');
        $conn->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_update');
    }
};
