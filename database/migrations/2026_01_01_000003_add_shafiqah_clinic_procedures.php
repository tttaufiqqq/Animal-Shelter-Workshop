<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('animals');
        $sqlDir = database_path('sql/shafiqah-animals/clinic_procedures');

        $conn->unprepared(require "$sqlDir/sp_clinic_create.php");
        $conn->unprepared(require "$sqlDir/sp_clinic_read.php");
        $conn->unprepared(require "$sqlDir/sp_clinic_read_all.php");
        $conn->unprepared(require "$sqlDir/sp_clinic_update.php");
        $conn->unprepared(require "$sqlDir/sp_clinic_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('animals');

        foreach (['sp_clinic_delete', 'sp_clinic_update', 'sp_clinic_read_all',
                  'sp_clinic_read', 'sp_clinic_create'] as $proc) {
            $conn->unprepared("DROP PROCEDURE IF EXISTS {$proc}");
        }
    }
};
