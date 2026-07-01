<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('animals');
        $sqlDir = database_path('sql/shafiqah-animals/validation_triggers');

        $conn->unprepared(require "$sqlDir/trg_vet_validate.php");
        $conn->unprepared(require "$sqlDir/trg_clinic_validate.php");
        $conn->unprepared(require "$sqlDir/trg_animal_validate.php");
        $conn->unprepared(require "$sqlDir/trg_medical_validate.php");
        $conn->unprepared(require "$sqlDir/trg_vaccination_validate.php");
    }

    public function down(): void
    {
        $conn = DB::connection('animals');

        $triggers = [
            'trg_vet_validate_insert', 'trg_vet_validate_update',
            'trg_clinic_validate_insert', 'trg_clinic_validate_update',
            'trg_animal_validate_insert', 'trg_animal_validate_update',
            'trg_medical_validate_insert', 'trg_medical_validate_update',
            'trg_vaccination_validate_insert', 'trg_vaccination_validate_update',
        ];

        foreach ($triggers as $trigger) {
            $conn->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
