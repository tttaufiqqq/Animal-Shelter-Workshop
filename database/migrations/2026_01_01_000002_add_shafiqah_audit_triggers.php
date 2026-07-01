<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * NOTE: Audit triggers DISABLED - auditing is now handled at the application layer
     * and written to the centralized audit_logs table in the taufiq (PostgreSQL) database.
     * MySQL triggers cannot insert into PostgreSQL databases.
     */
    public function up(): void
    {
        // DISABLED: Cross-database auditing not possible with MySQL triggers.
        // Auditing is handled in AnimalProcedureService.php via application-level logging.
        return;
    }

    public function down(): void
    {
        $connection = DB::connection('animals');

        $triggers = [
            'trg_animal_profile_after_delete',
            'trg_animal_profile_after_update',
            'trg_animal_profile_after_insert',
            'trg_vaccination_after_delete',
            'trg_vaccination_after_insert',
            'trg_medical_after_delete',
            'trg_medical_after_insert',
            'trg_animal_after_delete',
            'trg_animal_after_update',
            'trg_animal_after_insert',
            'trg_vet_after_delete',
            'trg_vet_after_update',
            'trg_vet_after_insert',
            'trg_clinic_after_delete',
            'trg_clinic_after_update',
            'trg_clinic_after_insert',
        ];

        foreach ($triggers as $trigger) {
            $connection->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
