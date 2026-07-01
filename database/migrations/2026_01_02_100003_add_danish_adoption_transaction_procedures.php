<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('booking');
        $sqlDir = database_path('sql/danish/adoption_transaction_procedures');

        $conn->unprepared(require "$sqlDir/sp_adoption_create.php");
        $conn->unprepared(require "$sqlDir/sp_adoption_read.php");
        $conn->unprepared(require "$sqlDir/sp_adoption_get_by_booking.php");
        $conn->unprepared(require "$sqlDir/sp_transaction_create.php");
        $conn->unprepared(require "$sqlDir/sp_transaction_read.php");
        $conn->unprepared(require "$sqlDir/sp_transaction_update_status.php");
        $conn->unprepared(require "$sqlDir/sp_transaction_get_by_bill_code.php");
        $conn->unprepared(require "$sqlDir/sp_transaction_get_by_user.php");
    }

    public function down(): void
    {
        $conn = DB::connection('booking');

        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_read');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_get_by_booking');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_create');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_read');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_update_status');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_bill_code');
        $conn->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_user');
    }
};
