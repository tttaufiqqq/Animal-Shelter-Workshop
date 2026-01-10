<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data Validation Triggers for Shafiqah Database (MySQL)
     * These triggers validate data before INSERT and UPDATE operations.
     */
    public function up(): void
    {
        $connection = DB::connection('shafiqah');

        // ===========================
        // VET VALIDATION TRIGGERS
        // ===========================

        // Drop existing triggers first
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_vet_validate_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_vet_validate_update');

        // Validate vet data before INSERT
        $connection->unprepared("
            CREATE TRIGGER trg_vet_validate_insert
            BEFORE INSERT ON vet
            FOR EACH ROW
            BEGIN
                -- Validate name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vet name is required';
                END IF;

                -- Validate email format if provided
                IF NEW.email IS NOT NULL AND NEW.email != '' AND NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format for vet';
                END IF;

                -- Validate phone format if provided (Malaysian format: +60XX-XXXXXXX or 01X-XXXXXXX)
                IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
                END IF;

                -- Validate license number if provided
                IF NEW.license_no IS NOT NULL AND NEW.license_no != '' AND LENGTH(NEW.license_no) < 3 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'License number must be at least 3 characters';
                END IF;
            END
        ");

        // Validate vet data before UPDATE
        $connection->unprepared("
            CREATE TRIGGER trg_vet_validate_update
            BEFORE UPDATE ON vet
            FOR EACH ROW
            BEGIN
                -- Validate name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vet name is required';
                END IF;

                -- Validate email format if provided
                IF NEW.email IS NOT NULL AND NEW.email != '' AND NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format for vet';
                END IF;

                -- Validate phone format if provided
                IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
                END IF;

                -- Validate license number if provided
                IF NEW.license_no IS NOT NULL AND NEW.license_no != '' AND LENGTH(NEW.license_no) < 3 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'License number must be at least 3 characters';
                END IF;
            END
        ");

        // ===========================
        // CLINIC VALIDATION TRIGGERS
        // ===========================

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_clinic_validate_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_clinic_validate_update');

        // Validate clinic data before INSERT
        $connection->unprepared("
            CREATE TRIGGER trg_clinic_validate_insert
            BEFORE INSERT ON clinic
            FOR EACH ROW
            BEGIN
                -- Validate name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Clinic name is required';
                END IF;

                -- Validate latitude range if provided (-90 to 90)
                IF NEW.latitude IS NOT NULL AND (NEW.latitude < -90 OR NEW.latitude > 90) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Latitude must be between -90 and 90';
                END IF;

                -- Validate longitude range if provided (-180 to 180)
                IF NEW.longitude IS NOT NULL AND (NEW.longitude < -180 OR NEW.longitude > 180) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Longitude must be between -180 and 180';
                END IF;

                -- Validate phone format if provided
                IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
                END IF;
            END
        ");

        // Validate clinic data before UPDATE
        $connection->unprepared("
            CREATE TRIGGER trg_clinic_validate_update
            BEFORE UPDATE ON clinic
            FOR EACH ROW
            BEGIN
                -- Validate name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Clinic name is required';
                END IF;

                -- Validate latitude range if provided
                IF NEW.latitude IS NOT NULL AND (NEW.latitude < -90 OR NEW.latitude > 90) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Latitude must be between -90 and 90';
                END IF;

                -- Validate longitude range if provided
                IF NEW.longitude IS NOT NULL AND (NEW.longitude < -180 OR NEW.longitude > 180) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Longitude must be between -180 and 180';
                END IF;

                -- Validate phone format if provided
                IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
                END IF;
            END
        ");

        // ===========================
        // ANIMAL VALIDATION TRIGGERS
        // ===========================

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_animal_validate_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_animal_validate_update');

        // Validate animal data before INSERT
        $connection->unprepared("
            CREATE TRIGGER trg_animal_validate_insert
            BEFORE INSERT ON animal
            FOR EACH ROW
            BEGIN
                -- Validate name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal name is required';
                END IF;

                -- Validate species is not empty
                IF NEW.species IS NULL OR TRIM(NEW.species) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal species is required';
                END IF;

                -- Validate gender is valid
                IF NEW.gender IS NOT NULL AND NEW.gender NOT IN ('Male', 'Female', 'Unknown') THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Gender must be Male, Female, or Unknown';
                END IF;

                -- NOTE: age is VARCHAR, can contain text like 'puppy', '2 years', etc.
                -- No numeric validation needed

                -- Validate weight is positive if provided
                IF NEW.weight IS NOT NULL AND NEW.weight < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Weight cannot be negative';
                END IF;

                -- Validate adoption status
                IF NEW.adoption_status IS NOT NULL AND NEW.adoption_status NOT IN ('Available', 'Pending', 'Adopted', 'Not Adopted', 'Not Available') THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid adoption status. Must be: Available, Pending, Adopted, Not Adopted, or Not Available';
                END IF;

                -- Set default adoption status if not provided
                IF NEW.adoption_status IS NULL THEN
                    SET NEW.adoption_status = 'Not Adopted';
                END IF;

                -- Set default gender if not provided
                IF NEW.gender IS NULL THEN
                    SET NEW.gender = 'Unknown';
                END IF;
            END
        ");

        // Validate animal data before UPDATE
        $connection->unprepared("
            CREATE TRIGGER trg_animal_validate_update
            BEFORE UPDATE ON animal
            FOR EACH ROW
            BEGIN
                -- Validate name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal name is required';
                END IF;

                -- Validate species is not empty
                IF NEW.species IS NULL OR TRIM(NEW.species) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal species is required';
                END IF;

                -- Validate gender is valid
                IF NEW.gender IS NOT NULL AND NEW.gender NOT IN ('Male', 'Female', 'Unknown') THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Gender must be Male, Female, or Unknown';
                END IF;

                -- NOTE: age is VARCHAR, can contain text like 'puppy', '2 years', etc.
                -- No numeric validation needed

                -- Validate weight is positive if provided
                IF NEW.weight IS NOT NULL AND NEW.weight < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Weight cannot be negative';
                END IF;

                -- Validate adoption status
                IF NEW.adoption_status IS NOT NULL AND NEW.adoption_status NOT IN ('Available', 'Pending', 'Adopted', 'Not Adopted', 'Not Available') THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid adoption status. Must be: Available, Pending, Adopted, Not Adopted, or Not Available';
                END IF;
            END
        ");

        // ===========================
        // MEDICAL VALIDATION TRIGGERS
        // ===========================

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_medical_validate_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_medical_validate_update');

        // Validate medical data before INSERT
        $connection->unprepared("
            CREATE TRIGGER trg_medical_validate_insert
            BEFORE INSERT ON medical
            FOR EACH ROW
            BEGIN
                -- Validate animalID is provided
                IF NEW.animalID IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for medical record';
                END IF;

                -- Validate treatment_type is not empty
                IF NEW.treatment_type IS NULL OR TRIM(NEW.treatment_type) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Treatment type is required';
                END IF;

                -- Validate costs is not negative if provided
                IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Medical costs cannot be negative';
                END IF;
            END
        ");

        // Validate medical data before UPDATE
        $connection->unprepared("
            CREATE TRIGGER trg_medical_validate_update
            BEFORE UPDATE ON medical
            FOR EACH ROW
            BEGIN
                -- Validate animalID is provided
                IF NEW.animalID IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for medical record';
                END IF;

                -- Validate treatment_type is not empty
                IF NEW.treatment_type IS NULL OR TRIM(NEW.treatment_type) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Treatment type is required';
                END IF;

                -- Validate costs is not negative if provided
                IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Medical costs cannot be negative';
                END IF;
            END
        ");

        // ===========================
        // VACCINATION VALIDATION TRIGGERS
        // ===========================

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_vaccination_validate_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_vaccination_validate_update');

        // Validate vaccination data before INSERT
        $connection->unprepared("
            CREATE TRIGGER trg_vaccination_validate_insert
            BEFORE INSERT ON vaccination
            FOR EACH ROW
            BEGIN
                -- Validate animalID is provided
                IF NEW.animalID IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for vaccination record';
                END IF;

                -- Validate vaccination name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination name is required';
                END IF;

                -- Validate costs is not negative if provided
                IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination costs cannot be negative';
                END IF;

                -- Validate next_due_date is not in the past if provided
                IF NEW.next_due_date IS NOT NULL AND NEW.next_due_date < CURDATE() THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Next due date cannot be in the past';
                END IF;
            END
        ");

        // Validate vaccination data before UPDATE
        $connection->unprepared("
            CREATE TRIGGER trg_vaccination_validate_update
            BEFORE UPDATE ON vaccination
            FOR EACH ROW
            BEGIN
                -- Validate animalID is provided
                IF NEW.animalID IS NULL THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for vaccination record';
                END IF;

                -- Validate vaccination name is not empty
                IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination name is required';
                END IF;

                -- Validate costs is not negative if provided
                IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination costs cannot be negative';
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

        // Drop all validation triggers
        $triggers = [
            'trg_vet_validate_insert',
            'trg_vet_validate_update',
            'trg_clinic_validate_insert',
            'trg_clinic_validate_update',
            'trg_animal_validate_insert',
            'trg_animal_validate_update',
            'trg_medical_validate_insert',
            'trg_medical_validate_update',
            'trg_vaccination_validate_insert',
            'trg_vaccination_validate_update',
        ];

        foreach ($triggers as $trigger) {
            $connection->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
