<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('animals');
        $sqlDir = database_path('sql/shafiqah/vet_procedures');

        $conn->unprepared(require "$sqlDir/sp_vet_create.php");
        $conn->unprepared(require "$sqlDir/sp_vet_read_all.php");
        $conn->unprepared(require "$sqlDir/sp_vet_update.php");
        $conn->unprepared(require "$sqlDir/sp_vet_delete.php");
    }

    public function down(): void
    {
        $conn = DB::connection('animals');

        foreach (['sp_vet_delete', 'sp_vet_update', 'sp_vet_read_all', 'sp_vet_create'] as $proc) {
            $conn->unprepared("DROP PROCEDURE IF EXISTS {$proc}");
        }
    }
};
