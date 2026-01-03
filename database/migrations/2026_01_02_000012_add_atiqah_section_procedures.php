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
        // sp_section_create
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_section_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_section_create(
                IN p_name VARCHAR(255),
                IN p_description VARCHAR(1000),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_section_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_section_id = NULL;
                END;

                START TRANSACTION;

                -- Validation
                IF p_name IS NULL OR TRIM(p_name) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Section name is required';
                    SET o_section_id = NULL;
                    ROLLBACK;
                ELSEIF p_description IS NULL OR TRIM(p_description) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Section description is required';
                    SET o_section_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Insert section
                    INSERT INTO section (
                        name, description, created_at, updated_at
                    )
                    VALUES (
                        p_name, p_description, NOW(), NOW()
                    );

                    SET o_section_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Section created successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_section_read
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_section_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_section_read(
                IN p_section_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    name,
                    description,
                    created_at,
                    updated_at
                FROM section
                WHERE id = p_section_id;
            END
        ");

        // ===========================
        // sp_section_update
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_section_update
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_section_update(
                IN p_section_id BIGINT,
                IN p_name VARCHAR(255),
                IN p_description VARCHAR(1000),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if section exists
                SELECT COUNT(*) INTO v_exists FROM section WHERE id = p_section_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Section not found';
                    ROLLBACK;
                ELSEIF p_name IS NULL OR TRIM(p_name) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Section name is required';
                    ROLLBACK;
                ELSEIF p_description IS NULL OR TRIM(p_description) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Section description is required';
                    ROLLBACK;
                ELSE
                    -- Update section
                    UPDATE section
                    SET name = p_name,
                        description = p_description,
                        updated_at = NOW()
                    WHERE id = p_section_id;

                    SET o_status = 'success';
                    SET o_message = 'Section updated successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_section_delete
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_section_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_section_delete(
                IN p_section_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_has_slots BOOLEAN,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_slot_count INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_has_slots = FALSE;
                END;

                START TRANSACTION;

                -- Check if section exists
                SELECT COUNT(*) INTO v_exists FROM section WHERE id = p_section_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Section not found';
                    SET o_has_slots = FALSE;
                    ROLLBACK;
                ELSE
                    -- Check if section has slots
                    SELECT COUNT(*) INTO v_slot_count
                    FROM slot
                    WHERE sectionID = p_section_id;

                    IF v_slot_count > 0 THEN
                        -- Don't delete, just flag it
                        SET o_status = 'error';
                        SET o_message = 'Cannot delete section with existing slots. Please delete or move the slots first.';
                        SET o_has_slots = TRUE;
                        ROLLBACK;
                    ELSE
                        -- Safe to delete
                        DELETE FROM section WHERE id = p_section_id;

                        SET o_status = 'success';
                        SET o_message = 'Section deleted successfully';
                        SET o_has_slots = FALSE;
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

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_section_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_section_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_section_update');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_section_delete');
    }
};
