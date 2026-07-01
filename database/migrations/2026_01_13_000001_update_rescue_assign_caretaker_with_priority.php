<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Updates sp_rescue_assign_caretaker to accept priority parameter
     * so that urgency from report description is properly mapped to rescue priority.
     */
    public function up(): void
    {
        $conn   = DB::connection('reporting');
        $sqlDir = database_path('sql/eilya-reporting/rescue_caretaker_priority');

        $conn->unprepared(require "$sqlDir/sp_rescue_assign_caretaker_with_priority.php");
    }

    public function down(): void
    {
        $conn   = DB::connection('reporting');
        $sqlDir = database_path('sql/eilya-reporting/rescue_caretaker_priority');

        // Restore original procedure without priority parameter
        $conn->unprepared(require "$sqlDir/sp_rescue_assign_caretaker_original.php");
    }
};
