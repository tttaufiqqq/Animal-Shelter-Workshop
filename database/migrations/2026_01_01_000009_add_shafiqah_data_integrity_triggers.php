<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = DB::connection('shafiqah');

        // ===========================
        // MEDICAL IMMUTABILITY TRIGGERS
        // ===========================

        // Prevent UPDATE on medical records (immutable once created)
        $connection->unprepared("
            CREATE TRIGGER trg_medical_prevent_update
            BEFORE UPDATE ON medical
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Medical records are immutable and cannot be updated';
            END
        ");

        // Prevent DELETE on medical records (allow only CASCADE from animal deletion)
        $connection->unprepared("
            CREATE TRIGGER trg_medical_prevent_delete
            BEFORE DELETE ON medical
            FOR EACH ROW
            BEGIN
                -- Allow deletion only if it's part of a cascade (check if animal still exists)
                IF EXISTS (SELECT 1 FROM animal WHERE id = OLD.animalID) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Medical records are immutable and cannot be deleted directly';
                END IF;
            END
        ");

        // ===========================
        // VACCINATION IMMUTABILITY TRIGGERS
        // ===========================

        // Prevent UPDATE on vaccination records (immutable once created)
        $connection->unprepared("
            CREATE TRIGGER trg_vaccination_prevent_update
            BEFORE UPDATE ON vaccination
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Vaccination records are immutable and cannot be updated';
            END
        ");

        // Prevent DELETE on vaccination records (allow only CASCADE from animal deletion)
        $connection->unprepared("
            CREATE TRIGGER trg_vaccination_prevent_delete
            BEFORE DELETE ON vaccination
            FOR EACH ROW
            BEGIN
                -- Allow deletion only if it's part of a cascade (check if animal still exists)
                IF EXISTS (SELECT 1 FROM animal WHERE id = OLD.animalID) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Vaccination records are immutable and cannot be deleted directly';
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('shafiqah');

        $triggers = [
            'trg_vaccination_prevent_delete',
            'trg_vaccination_prevent_update',
            'trg_medical_prevent_delete',
            'trg_medical_prevent_update',
        ];

        foreach ($triggers as $trigger) {
            $connection->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
