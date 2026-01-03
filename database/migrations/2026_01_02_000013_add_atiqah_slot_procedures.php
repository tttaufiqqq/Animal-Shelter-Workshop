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
        $connection = DB::connection('atiqah');

        // ===========================
        // sp_slot_create
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_slot_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_slot_create(
                IN p_name VARCHAR(255),
                IN p_section_id BIGINT,
                IN p_capacity INT,
                IN p_status VARCHAR(50),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_slot_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_section_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_slot_id = NULL;
                END;

                START TRANSACTION;

                -- Validation
                IF p_name IS NULL OR TRIM(p_name) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Slot name is required';
                    SET o_slot_id = NULL;
                    ROLLBACK;
                ELSEIF p_section_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Section ID is required';
                    SET o_slot_id = NULL;
                    ROLLBACK;
                ELSEIF p_capacity IS NULL OR p_capacity < 1 THEN
                    SET o_status = 'error';
                    SET o_message = 'Capacity must be at least 1';
                    SET o_slot_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Check if section exists
                    SELECT COUNT(*) INTO v_section_exists
                    FROM section
                    WHERE id = p_section_id;

                    IF v_section_exists = 0 THEN
                        SET o_status = 'error';
                        SET o_message = 'The selected section does not exist';
                        SET o_slot_id = NULL;
                        ROLLBACK;
                    ELSE
                        -- Insert slot
                        INSERT INTO slot (
                            name, sectionID, capacity, status, created_at, updated_at
                        )
                        VALUES (
                            p_name, p_section_id, p_capacity,
                            COALESCE(p_status, 'available'), NOW(), NOW()
                        );

                        SET o_slot_id = LAST_INSERT_ID();
                        SET o_status = 'success';
                        SET o_message = 'Slot created successfully';
                        COMMIT;
                    END IF;
                END IF;
            END
        ");

        // ===========================
        // sp_slot_read
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_slot_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_slot_read(
                IN p_slot_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    name,
                    sectionID,
                    capacity,
                    status,
                    created_at,
                    updated_at
                FROM slot
                WHERE id = p_slot_id;
            END
        ");

        // ===========================
        // sp_slot_update
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_slot_update
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_slot_update(
                IN p_slot_id BIGINT,
                IN p_name VARCHAR(255),
                IN p_section_id BIGINT,
                IN p_capacity INT,
                IN p_status VARCHAR(50),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_section_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if slot exists
                SELECT COUNT(*) INTO v_exists FROM slot WHERE id = p_slot_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Slot not found';
                    ROLLBACK;
                ELSEIF p_name IS NULL OR TRIM(p_name) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Slot name is required';
                    ROLLBACK;
                ELSEIF p_section_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Section ID is required';
                    ROLLBACK;
                ELSEIF p_capacity IS NULL OR p_capacity < 1 THEN
                    SET o_status = 'error';
                    SET o_message = 'Capacity must be at least 1';
                    ROLLBACK;
                ELSE
                    -- Check if section exists
                    SELECT COUNT(*) INTO v_section_exists
                    FROM section
                    WHERE id = p_section_id;

                    IF v_section_exists = 0 THEN
                        SET o_status = 'error';
                        SET o_message = 'The selected section does not exist';
                        ROLLBACK;
                    ELSE
                        -- Update slot
                        -- Note: Status auto-calculation based on animal count is handled
                        -- at the application layer due to cross-database constraints
                        UPDATE slot
                        SET name = p_name,
                            sectionID = p_section_id,
                            capacity = p_capacity,
                            status = COALESCE(p_status, status),
                            updated_at = NOW()
                        WHERE id = p_slot_id;

                        SET o_status = 'success';
                        SET o_message = 'Slot updated successfully';
                        COMMIT;
                    END IF;
                END IF;
            END
        ");

        // ===========================
        // sp_slot_delete
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_slot_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_slot_delete(
                IN p_slot_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_has_animals BOOLEAN,
                OUT o_animal_count INT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_inventory_count INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_has_animals = FALSE;
                    SET o_animal_count = 0;
                END;

                START TRANSACTION;

                -- Initialize output parameters
                SET o_has_animals = FALSE;
                SET o_animal_count = 0;

                -- Check if slot exists
                SELECT COUNT(*) INTO v_exists FROM slot WHERE id = p_slot_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Slot not found';
                    ROLLBACK;
                ELSE
                    -- Note: Animal count check is performed at application layer
                    -- because animals are in a different database (Shafiqah)
                    -- This procedure only handles inventory within the same database

                    -- Check if slot has inventory items
                    SELECT COUNT(*) INTO v_inventory_count
                    FROM inventory
                    WHERE slotID = p_slot_id;

                    IF v_inventory_count > 0 THEN
                        SET o_status = 'error';
                        SET o_message = CONCAT('Cannot delete slot with existing inventory items. Please reassign or delete ', v_inventory_count, ' inventory item(s) first.');
                        ROLLBACK;
                    ELSE
                        -- Safe to delete (from database perspective)
                        -- Animal validation must be done before calling this procedure
                        DELETE FROM slot WHERE id = p_slot_id;

                        SET o_status = 'success';
                        SET o_message = 'Slot deleted successfully';
                        COMMIT;
                    END IF;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('atiqah');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_slot_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_slot_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_slot_update');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_slot_delete');
    }
};
