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
        // sp_animal_create
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_create
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_create(
                IN p_name VARCHAR(255),
                IN p_species VARCHAR(255),
                IN p_health_details TEXT,
                IN p_age VARCHAR(255),
                IN p_gender ENUM('Male', 'Female', 'Unknown'),
                IN p_weight DECIMAL(8,2),
                IN p_adoption_status VARCHAR(255),
                IN p_rescue_id BIGINT,
                IN p_slot_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_animal_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_animal_id = NULL;
                END;

                START TRANSACTION;

                -- Insert animal (NO validation of rescueID/slotID - application layer handles this)
                INSERT INTO animal (
                    name, species, health_details, age, gender, weight,
                    adoption_status, rescueID, slotID, created_at, updated_at
                )
                VALUES (
                    p_name, p_species, p_health_details, p_age, p_gender, p_weight,
                    COALESCE(p_adoption_status, 'Not Adopted'), p_rescue_id, p_slot_id, NOW(), NOW()
                );

                SET o_animal_id = LAST_INSERT_ID();
                SET o_status = 'success';
                SET o_message = CONCAT('Animal \"', p_name, '\" created successfully');

                COMMIT;
            END
        ");

        // ===========================
        // sp_animal_read
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_read
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_read(
                IN p_animal_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    name,
                    species,
                    health_details,
                    age,
                    gender,
                    weight,
                    adoption_status,
                    rescueID,
                    slotID,
                    created_at,
                    updated_at
                FROM animal
                WHERE id = p_animal_id;
            END
        ");

        // ===========================
        // sp_animal_read_paginated
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_read_paginated
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_read_paginated(
                IN p_search VARCHAR(255),
                IN p_species VARCHAR(255),
                IN p_health_details VARCHAR(255),
                IN p_adoption_status VARCHAR(255),
                IN p_gender VARCHAR(20),
                IN p_rescue_ids TEXT,
                IN p_offset INT,
                IN p_limit INT
            )
            BEGIN
                -- Return paginated results
                SELECT
                    id,
                    name,
                    species,
                    health_details,
                    age,
                    gender,
                    weight,
                    adoption_status,
                    rescueID,
                    slotID,
                    created_at,
                    updated_at
                FROM animal
                WHERE
                    (p_search IS NULL OR LOWER(name) LIKE CONCAT('%', LOWER(p_search), '%'))
                    AND (p_species IS NULL OR LOWER(species) LIKE CONCAT('%', LOWER(p_species), '%'))
                    AND (p_health_details IS NULL OR health_details = p_health_details)
                    AND (p_adoption_status IS NULL OR adoption_status = p_adoption_status)
                    AND (p_gender IS NULL OR gender = p_gender)
                    AND (p_rescue_ids IS NULL OR FIND_IN_SET(rescueID, p_rescue_ids) > 0)
                ORDER BY created_at DESC
                LIMIT p_limit OFFSET p_offset;

                -- Return total count for pagination
                SELECT COUNT(*) as total_count
                FROM animal
                WHERE
                    (p_search IS NULL OR LOWER(name) LIKE CONCAT('%', LOWER(p_search), '%'))
                    AND (p_species IS NULL OR LOWER(species) LIKE CONCAT('%', LOWER(p_species), '%'))
                    AND (p_health_details IS NULL OR health_details = p_health_details)
                    AND (p_adoption_status IS NULL OR adoption_status = p_adoption_status)
                    AND (p_gender IS NULL OR gender = p_gender)
                    AND (p_rescue_ids IS NULL OR FIND_IN_SET(rescueID, p_rescue_ids) > 0);
            END
        ");

        // ===========================
        // sp_animal_update
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_update
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_update(
                IN p_animal_id BIGINT,
                IN p_name VARCHAR(255),
                IN p_species VARCHAR(255),
                IN p_health_details TEXT,
                IN p_age VARCHAR(255),
                IN p_gender ENUM('Male', 'Female', 'Unknown'),
                IN p_weight DECIMAL(8,2),
                IN p_slot_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if animal exists
                IF NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found';
                    ROLLBACK;
                ELSE
                    UPDATE animal
                    SET
                        name = p_name,
                        species = p_species,
                        health_details = p_health_details,
                        age = p_age,
                        gender = p_gender,
                        weight = p_weight,
                        slotID = p_slot_id,
                        updated_at = NOW()
                    WHERE id = p_animal_id;

                    SET o_status = 'success';
                    SET o_message = CONCAT('Animal \"', p_name, '\" updated successfully');
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_animal_delete
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_delete
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_delete(
                IN p_animal_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_animal_name VARCHAR(255),
                OUT o_slot_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_animal_name = NULL;
                    SET o_slot_id = NULL;
                END;

                START TRANSACTION;

                -- Get animal details before deletion
                SELECT name, slotID INTO o_animal_name, o_slot_id
                FROM animal
                WHERE id = p_animal_id;

                IF o_animal_name IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found';
                    ROLLBACK;
                ELSE
                    -- Delete animal (CASCADE will delete medical, vaccination, animal_profile via FK)
                    DELETE FROM animal WHERE id = p_animal_id;

                    SET o_status = 'success';
                    SET o_message = CONCAT('Animal \"', o_animal_name, '\" deleted successfully');
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_animal_assign_slot
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_animal_assign_slot
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_animal_assign_slot(
                IN p_animal_id BIGINT,
                IN p_slot_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_previous_slot_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_previous_slot_id = NULL;
                END;

                START TRANSACTION;

                -- Get previous slot ID
                SELECT slotID INTO o_previous_slot_id FROM animal WHERE id = p_animal_id;

                IF o_previous_slot_id IS NULL AND NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found';
                    ROLLBACK;
                ELSE
                    -- Update animal slot
                    UPDATE animal
                    SET slotID = p_slot_id, updated_at = NOW()
                    WHERE id = p_animal_id;

                    SET o_status = 'success';
                    SET o_message = 'Slot assigned successfully';
                    COMMIT;
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

        $procedures = [
            'sp_animal_assign_slot',
            'sp_animal_delete',
            'sp_animal_update',
            'sp_animal_read_paginated',
            'sp_animal_read',
            'sp_animal_create',
        ];

        foreach ($procedures as $procedure) {
            $connection->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }
};
