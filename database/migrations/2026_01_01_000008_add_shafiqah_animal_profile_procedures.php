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
        // sp_animal_profile_upsert
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_profile_upsert
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_profile_upsert(
                IN p_animal_id BIGINT,
                IN p_age VARCHAR(50),
                IN p_size ENUM('small', 'medium', 'large'),
                IN p_energy_level ENUM('low', 'medium', 'high'),
                IN p_good_with_kids BOOLEAN,
                IN p_good_with_pets BOOLEAN,
                IN p_temperament ENUM('calm', 'active', 'shy', 'friendly', 'independent'),
                IN p_medical_needs ENUM('none', 'minor', 'moderate', 'special'),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_profile_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_existing_profile_id BIGINT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_profile_id = NULL;
                END;

                START TRANSACTION;

                -- Check if animal exists
                IF NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found';
                    SET o_profile_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Check if profile exists
                    SELECT id INTO v_existing_profile_id FROM animal_profile WHERE animalID = p_animal_id;

                    IF v_existing_profile_id IS NULL THEN
                        -- INSERT
                        INSERT INTO animal_profile (
                            animalID, age, size, energy_level, good_with_kids, good_with_pets,
                            temperament, medical_needs, created_at, updated_at
                        )
                        VALUES (
                            p_animal_id, p_age, p_size, p_energy_level, p_good_with_kids, p_good_with_pets,
                            p_temperament, p_medical_needs, NOW(), NOW()
                        );

                        SET o_profile_id = LAST_INSERT_ID();
                        SET o_status = 'success';
                        SET o_message = 'Animal profile created successfully';
                    ELSE
                        -- UPDATE
                        UPDATE animal_profile
                        SET
                            age = p_age,
                            size = p_size,
                            energy_level = p_energy_level,
                            good_with_kids = p_good_with_kids,
                            good_with_pets = p_good_with_pets,
                            temperament = p_temperament,
                            medical_needs = p_medical_needs,
                            updated_at = NOW()
                        WHERE id = v_existing_profile_id;

                        SET o_profile_id = v_existing_profile_id;
                        SET o_status = 'success';
                        SET o_message = 'Animal profile updated successfully';
                    END IF;

                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_animal_profile_read
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_profile_read
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_profile_read(
                IN p_animal_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    animalID,
                    age,
                    size,
                    energy_level,
                    good_with_kids,
                    good_with_pets,
                    temperament,
                    medical_needs,
                    created_at,
                    updated_at
                FROM animal_profile
                WHERE animalID = p_animal_id;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('shafiqah');

        $procedures = [
            'sp_animal_profile_read',
            'sp_animal_profile_upsert',
        ];

        foreach ($procedures as $procedure) {
            $connection->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }
};
