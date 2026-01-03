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
        $connection = DB::connection('eilya');

        // ===========================
        // sp_report_create
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_report_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_report_create(
                IN p_latitude DECIMAL(10,8),
                IN p_longitude DECIMAL(11,8),
                IN p_address VARCHAR(255),
                IN p_city VARCHAR(100),
                IN p_state VARCHAR(100),
                IN p_report_status VARCHAR(50),
                IN p_description TEXT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_report_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_report_id = NULL;
                END;

                START TRANSACTION;

                -- Validation
                IF p_latitude IS NULL OR p_longitude IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Latitude and longitude are required';
                    SET o_report_id = NULL;
                    ROLLBACK;
                ELSEIF p_address IS NULL OR TRIM(p_address) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Address is required';
                    SET o_report_id = NULL;
                    ROLLBACK;
                ELSEIF p_city IS NULL OR TRIM(p_city) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'City is required';
                    SET o_report_id = NULL;
                    ROLLBACK;
                ELSEIF p_description IS NULL OR TRIM(p_description) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Description is required';
                    SET o_report_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Insert report
                    INSERT INTO report (
                        latitude, longitude, address, city, state, report_status,
                        description, userID, created_at, updated_at
                    )
                    VALUES (
                        p_latitude, p_longitude, p_address, p_city, p_state,
                        COALESCE(p_report_status, 'Pending'),
                        p_description, p_user_id, NOW(), NOW()
                    );

                    SET o_report_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Report created successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_report_read
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_report_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_report_read(
                IN p_report_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    latitude,
                    longitude,
                    address,
                    city,
                    state,
                    report_status,
                    description,
                    userID,
                    created_at,
                    updated_at
                FROM report
                WHERE id = p_report_id;
            END
        ");

        // ===========================
        // sp_report_read_paginated
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_report_read_paginated
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_report_read_paginated(
                IN p_user_id BIGINT,
                IN p_status VARCHAR(50),
                IN p_city VARCHAR(100),
                IN p_offset INT,
                IN p_limit INT,
                OUT o_total_count INT
            )
            BEGIN
                -- Get total count
                SELECT COUNT(*) INTO o_total_count
                FROM report
                WHERE (p_user_id IS NULL OR userID = p_user_id)
                  AND (p_status IS NULL OR report_status = p_status)
                  AND (p_city IS NULL OR city = p_city);

                -- Get paginated data (returned as result set)
                SELECT
                    id,
                    latitude,
                    longitude,
                    address,
                    city,
                    state,
                    report_status,
                    description,
                    userID,
                    created_at,
                    updated_at
                FROM report
                WHERE (p_user_id IS NULL OR userID = p_user_id)
                  AND (p_status IS NULL OR report_status = p_status)
                  AND (p_city IS NULL OR city = p_city)
                ORDER BY created_at DESC
                LIMIT p_limit OFFSET p_offset;
            END
        ");

        // ===========================
        // sp_report_update_status
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_report_update_status
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_report_update_status(
                IN p_report_id BIGINT,
                IN p_new_status VARCHAR(50),
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

                -- Check if report exists
                SELECT COUNT(*) INTO v_exists FROM report WHERE id = p_report_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Report not found';
                    ROLLBACK;
                ELSE
                    -- Update report status
                    UPDATE report
                    SET report_status = p_new_status,
                        updated_at = NOW()
                    WHERE id = p_report_id;

                    SET o_status = 'success';
                    SET o_message = 'Report status updated successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_report_delete
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_report_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_report_delete(
                IN p_report_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_has_rescue BOOLEAN,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_rescue_count INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_has_rescue = FALSE;
                END;

                START TRANSACTION;

                -- Check if report exists
                SELECT COUNT(*) INTO v_exists FROM report WHERE id = p_report_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Report not found';
                    SET o_has_rescue = FALSE;
                    ROLLBACK;
                ELSE
                    -- Check if rescue exists for this report
                    SELECT COUNT(*) INTO v_rescue_count
                    FROM rescue
                    WHERE reportID = p_report_id;

                    IF v_rescue_count > 0 THEN
                        -- Don't delete, just flag it
                        SET o_status = 'error';
                        SET o_message = 'Cannot delete report with associated rescue. Please delete the rescue first.';
                        SET o_has_rescue = TRUE;
                        ROLLBACK;
                    ELSE
                        -- Safe to delete (images will cascade delete via FK)
                        DELETE FROM report WHERE id = p_report_id;

                        SET o_status = 'success';
                        SET o_message = 'Report deleted successfully';
                        SET o_has_rescue = FALSE;
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
        $connection = DB::connection('eilya');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_report_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_report_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_report_read_paginated');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_report_update_status');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_report_delete');
    }
};
