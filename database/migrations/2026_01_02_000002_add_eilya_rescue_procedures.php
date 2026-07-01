<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('reporting');
        $sqlDir = database_path('sql/eilya-reporting/rescue_procedures');

        $conn->unprepared(require "$sqlDir/sp_rescue_assign_caretaker.php");
        $conn->unprepared(require "$sqlDir/sp_rescue_update_status.php");
        $conn->unprepared(require "$sqlDir/sp_rescue_update_priority.php");
        $conn->unprepared(require "$sqlDir/sp_rescue_read_by_caretaker.php");
        $conn->unprepared(require "$sqlDir/sp_rescue_get_status_counts.php");
        $conn->unprepared(require "$sqlDir/sp_rescue_create.php");
    }

    public function down(): void
    {
        $conn = DB::connection('reporting');

        foreach (['sp_rescue_assign_caretaker', 'sp_rescue_update_status',
                  'sp_rescue_update_priority', 'sp_rescue_read_by_caretaker',
                  'sp_rescue_get_status_counts', 'sp_rescue_create'] as $proc) {
            $conn->unprepared("DROP PROCEDURE IF EXISTS {$proc}");
        }
    }
};
