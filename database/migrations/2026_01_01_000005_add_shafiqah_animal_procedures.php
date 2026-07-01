<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('animals');
        $sqlDir = database_path('sql/shafiqah-animals/animal_procedures');

        $conn->unprepared(require "$sqlDir/sp_animal_create.php");
        $conn->unprepared(require "$sqlDir/sp_animal_read.php");
        $conn->unprepared(require "$sqlDir/sp_animal_read_paginated.php");
        $conn->unprepared(require "$sqlDir/sp_animal_update.php");
        $conn->unprepared(require "$sqlDir/sp_animal_delete.php");
        $conn->unprepared(require "$sqlDir/sp_animal_assign_slot.php");
    }

    public function down(): void
    {
        $conn = DB::connection('animals');

        foreach (['sp_animal_assign_slot', 'sp_animal_delete', 'sp_animal_update',
                  'sp_animal_read_paginated', 'sp_animal_read', 'sp_animal_create'] as $proc) {
            $conn->unprepared("DROP PROCEDURE IF EXISTS {$proc}");
        }
    }
};
