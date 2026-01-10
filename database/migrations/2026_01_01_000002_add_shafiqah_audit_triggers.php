<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: Audit triggers DISABLED - auditing is now handled at the application layer
     * and written to the centralized audit_logs table in the taufiq (PostgreSQL) database.
     * MySQL triggers cannot insert into PostgreSQL databases.
     */
    public function up(): void
    {
        // DISABLED: Cross-database auditing not possible with MySQL triggers
        // Auditing is now handled in ShafiqahProcedureService.php via application-level logging
        // to the taufiq database's audit_logs table.
        return;

        $connection = DB::connection('shafiqah');

        // ===========================
        // CLINIC AUDIT TRIGGERS
        // ===========================

        $connection->unprepared("
            CREATE TRIGGER trg_clinic_after_insert
            AFTER INSERT ON clinic
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'clinic', 'created', 'Clinic', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', NEW.name,
                        'address', NEW.address,
                        'contactNum', NEW.contactNum,
                        'latitude', NEW.latitude,
                        'longitude', NEW.longitude
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_clinic_after_update
            AFTER UPDATE ON clinic
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'clinic', 'updated', 'Clinic', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', OLD.name,
                        'address', OLD.address,
                        'contactNum', OLD.contactNum,
                        'latitude', OLD.latitude,
                        'longitude', OLD.longitude
                    ),
                    JSON_OBJECT(
                        'name', NEW.name,
                        'address', NEW.address,
                        'contactNum', NEW.contactNum,
                        'latitude', NEW.latitude,
                        'longitude', NEW.longitude
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_clinic_after_delete
            AFTER DELETE ON clinic
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'clinic', 'deleted', 'Clinic', OLD.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', OLD.name,
                        'address', OLD.address,
                        'contactNum', OLD.contactNum
                    ),
                    'success'
                );
            END
        ");

        // ===========================
        // VET AUDIT TRIGGERS
        // ===========================

        $connection->unprepared("
            CREATE TRIGGER trg_vet_after_insert
            AFTER INSERT ON vet
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'vet', 'created', 'Vet', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', NEW.name,
                        'email', NEW.email,
                        'contactNum', NEW.contactNum,
                        'specialization', NEW.specialization,
                        'license_no', NEW.license_no,
                        'clinicID', NEW.clinicID
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_vet_after_update
            AFTER UPDATE ON vet
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'vet', 'updated', 'Vet', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', OLD.name,
                        'email', OLD.email,
                        'contactNum', OLD.contactNum,
                        'specialization', OLD.specialization,
                        'license_no', OLD.license_no,
                        'clinicID', OLD.clinicID
                    ),
                    JSON_OBJECT(
                        'name', NEW.name,
                        'email', NEW.email,
                        'contactNum', NEW.contactNum,
                        'specialization', NEW.specialization,
                        'license_no', NEW.license_no,
                        'clinicID', NEW.clinicID
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_vet_after_delete
            AFTER DELETE ON vet
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'vet', 'deleted', 'Vet', OLD.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', OLD.name,
                        'email', OLD.email,
                        'specialization', OLD.specialization,
                        'license_no', OLD.license_no
                    ),
                    'success'
                );
            END
        ");

        // ===========================
        // ANIMAL AUDIT TRIGGERS
        // ===========================

        $connection->unprepared("
            CREATE TRIGGER trg_animal_after_insert
            AFTER INSERT ON animal
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'animal', 'created', 'Animal', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', NEW.name,
                        'species', NEW.species,
                        'age', NEW.age,
                        'gender', NEW.gender,
                        'weight', NEW.weight,
                        'health_details', NEW.health_details,
                        'adoption_status', NEW.adoption_status,
                        'rescueID', NEW.rescueID,
                        'slotID', NEW.slotID
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_animal_after_update
            AFTER UPDATE ON animal
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, new_values, metadata, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'animal', 'updated', 'Animal', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', OLD.name,
                        'species', OLD.species,
                        'age', OLD.age,
                        'gender', OLD.gender,
                        'weight', OLD.weight,
                        'health_details', OLD.health_details,
                        'adoption_status', OLD.adoption_status,
                        'slotID', OLD.slotID
                    ),
                    JSON_OBJECT(
                        'name', NEW.name,
                        'species', NEW.species,
                        'age', NEW.age,
                        'gender', NEW.gender,
                        'weight', NEW.weight,
                        'health_details', NEW.health_details,
                        'adoption_status', NEW.adoption_status,
                        'slotID', NEW.slotID
                    ),
                    JSON_OBJECT(
                        'adoption_status_changed', IF(OLD.adoption_status != NEW.adoption_status, TRUE, FALSE),
                        'slot_changed', IF(OLD.slotID != NEW.slotID, TRUE, FALSE)
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_animal_after_delete
            AFTER DELETE ON animal
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'animal', 'deleted', 'Animal', OLD.id,
                    NOW(),
                    JSON_OBJECT(
                        'name', OLD.name,
                        'species', OLD.species,
                        'adoption_status', OLD.adoption_status,
                        'rescueID', OLD.rescueID,
                        'slotID', OLD.slotID
                    ),
                    'success'
                );
            END
        ");

        // ===========================
        // MEDICAL AUDIT TRIGGERS
        // ===========================

        $connection->unprepared("
            CREATE TRIGGER trg_medical_after_insert
            AFTER INSERT ON medical
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'medical', 'created', 'Medical', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'animalID', NEW.animalID,
                        'treatment_type', NEW.treatment_type,
                        'diagnosis', NEW.diagnosis,
                        'action', NEW.action,
                        'vetID', NEW.vetID,
                        'costs', NEW.costs
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_medical_after_delete
            AFTER DELETE ON medical
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, metadata, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'medical', 'deleted', 'Medical', OLD.id,
                    NOW(),
                    JSON_OBJECT(
                        'animalID', OLD.animalID,
                        'treatment_type', OLD.treatment_type,
                        'diagnosis', OLD.diagnosis,
                        'costs', OLD.costs
                    ),
                    JSON_OBJECT(
                        'note', 'Deleted via cascade from animal deletion'
                    ),
                    'success'
                );
            END
        ");

        // ===========================
        // VACCINATION AUDIT TRIGGERS
        // ===========================

        $connection->unprepared("
            CREATE TRIGGER trg_vaccination_after_insert
            AFTER INSERT ON vaccination
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'vaccination', 'created', 'Vaccination', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'animalID', NEW.animalID,
                        'name', NEW.name,
                        'type', NEW.type,
                        'next_due_date', NEW.next_due_date,
                        'vetID', NEW.vetID,
                        'costs', NEW.costs
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_vaccination_after_delete
            AFTER DELETE ON vaccination
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, metadata, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'vaccination', 'deleted', 'Vaccination', OLD.id,
                    NOW(),
                    JSON_OBJECT(
                        'animalID', OLD.animalID,
                        'name', OLD.name,
                        'type', OLD.type,
                        'next_due_date', OLD.next_due_date,
                        'costs', OLD.costs
                    ),
                    JSON_OBJECT(
                        'note', 'Deleted via cascade or SET NULL from animal/vet deletion'
                    ),
                    'success'
                );
            END
        ");

        // ===========================
        // ANIMAL PROFILE AUDIT TRIGGERS
        // ===========================

        $connection->unprepared("
            CREATE TRIGGER trg_animal_profile_after_insert
            AFTER INSERT ON animal_profile
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'animal_profile', 'created', 'AnimalProfile', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'animalID', NEW.animalID,
                        'age', NEW.age,
                        'size', NEW.size,
                        'energy_level', NEW.energy_level,
                        'good_with_kids', NEW.good_with_kids,
                        'good_with_pets', NEW.good_with_pets,
                        'temperament', NEW.temperament,
                        'medical_needs', NEW.medical_needs
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_animal_profile_after_update
            AFTER UPDATE ON animal_profile
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, new_values, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'animal_profile', 'updated', 'AnimalProfile', NEW.id,
                    NOW(),
                    JSON_OBJECT(
                        'age', OLD.age,
                        'size', OLD.size,
                        'energy_level', OLD.energy_level,
                        'good_with_kids', OLD.good_with_kids,
                        'good_with_pets', OLD.good_with_pets,
                        'temperament', OLD.temperament,
                        'medical_needs', OLD.medical_needs
                    ),
                    JSON_OBJECT(
                        'age', NEW.age,
                        'size', NEW.size,
                        'energy_level', NEW.energy_level,
                        'good_with_kids', NEW.good_with_kids,
                        'good_with_pets', NEW.good_with_pets,
                        'temperament', NEW.temperament,
                        'medical_needs', NEW.medical_needs
                    ),
                    'success'
                );
            END
        ");

        $connection->unprepared("
            CREATE TRIGGER trg_animal_profile_after_delete
            AFTER DELETE ON animal_profile
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    performed_at, old_values, metadata, status
                )
                VALUES (
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role,
                    'animal_profile', 'deleted', 'AnimalProfile', OLD.id,
                    NOW(),
                    JSON_OBJECT(
                        'animalID', OLD.animalID,
                        'size', OLD.size,
                        'energy_level', OLD.energy_level,
                        'temperament', OLD.temperament
                    ),
                    JSON_OBJECT(
                        'note', 'Deleted via cascade from animal deletion'
                    ),
                    'success'
                );
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('shafiqah');

        // Drop triggers in reverse order
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
