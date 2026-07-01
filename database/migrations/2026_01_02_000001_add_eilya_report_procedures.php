<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('reporting');
        $sqlDir = database_path('sql/eilya/report_procedures');

        $conn->unprepared(require "$sqlDir/sp_report_create.php");
        $conn->unprepared(require "$sqlDir/sp_report_read.php");
        $conn->unprepared(require "$sqlDir/sp_report_read_paginated.php");
        $conn->unprepared(require "$sqlDir/sp_report_update_status.php");
        $conn->unprepared(require "$sqlDir/sp_report_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('reporting');

        foreach (['sp_report_create', 'sp_report_read', 'sp_report_read_paginated',
                  'sp_report_update_status', 'sp_report_delete'] as $proc) {
            $conn->unprepared("DROP PROCEDURE IF EXISTS {$proc}");
        }
    }
};
