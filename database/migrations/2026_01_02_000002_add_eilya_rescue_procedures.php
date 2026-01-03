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
        // sp_rescue_assign_caretaker
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_rescue_assign_caretaker
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_rescue_assign_caretaker(
                IN p_report_id BIGINT,
                IN p_caretaker_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_rescue_id BIGINT,
                OUT o_is_reassignment BOOLEAN,
                OUT o_old_caretaker_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_report_exists INT;
                DECLARE v_existing_rescue_id BIGINT;
                DECLARE v_current_caretaker_id BIGINT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_rescue_id = NULL;
                    SET o_is_reassignment = FALSE;
                    SET o_old_caretaker_id = NULL;
                END;

                START TRANSACTION;

                -- Check if report exists
                SELECT COUNT(*) INTO v_report_exists FROM report WHERE id = p_report_id;

                IF v_report_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Report not found';
                    SET o_rescue_id = NULL;
                    SET o_is_reassignment = FALSE;
                    SET o_old_caretaker_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Check if rescue already exists for this report
                    SELECT id, caretakerID
                    INTO v_existing_rescue_id, v_current_caretaker_id
                    FROM rescue
                    WHERE reportID = p_report_id
                    LIMIT 1;

                    IF v_existing_rescue_id IS NOT NULL THEN
                        -- Rescue exists - this is a reassignment
                        IF v_current_caretaker_id = p_caretaker_id THEN
                            -- Cannot reassign to same caretaker
                            SET o_status = 'error';
                            SET o_message = 'This report is already assigned to the same caretaker';
                            SET o_rescue_id = v_existing_rescue_id;
                            SET o_is_reassignment = FALSE;
                            SET o_old_caretaker_id = v_current_caretaker_id;
                            ROLLBACK;
                        ELSE
                            -- Update to new caretaker
                            UPDATE rescue
                            SET caretakerID = p_caretaker_id,
                                updated_at = NOW()
                            WHERE id = v_existing_rescue_id;

                            SET o_rescue_id = v_existing_rescue_id;
                            SET o_is_reassignment = TRUE;
                            SET o_old_caretaker_id = v_current_caretaker_id;
                            SET o_status = 'success';
                            SET o_message = 'Caretaker reassigned successfully';
                            COMMIT;
                        END IF;
                    ELSE
                        -- No rescue exists - create new one
                        INSERT INTO rescue (
                            status, priority, remarks, reportID, caretakerID,
                            created_at, updated_at
                        )
                        VALUES (
                            'Scheduled', 'normal', NULL, p_report_id, p_caretaker_id,
                            NOW(), NOW()
                        );

                        SET o_rescue_id = LAST_INSERT_ID();
                        SET o_is_reassignment = FALSE;
                        SET o_old_caretaker_id = NULL;
                        SET o_status = 'success';
                        SET o_message = 'Caretaker assigned successfully';

                        -- Update report status to 'Assigned' (triggers will also do this)
                        UPDATE report
                        SET report_status = 'Assigned',
                            updated_at = NOW()
                        WHERE id = p_report_id;

                        COMMIT;
                    END IF;
                END IF;
            END
        ");

        // ===========================
        // sp_rescue_update_status
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_rescue_update_status
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_rescue_update_status(
                IN p_rescue_id BIGINT,
                IN p_new_status VARCHAR(50),
                IN p_remarks TEXT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_old_status VARCHAR(50),
                OUT o_report_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_current_status VARCHAR(50);
                DECLARE v_report_id BIGINT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_old_status = NULL;
                    SET o_report_id = NULL;
                END;

                START TRANSACTION;

                -- Check if rescue exists and get current status
                SELECT COUNT(*), MAX(status), MAX(reportID)
                INTO v_exists, v_current_status, v_report_id
                FROM rescue
                WHERE id = p_rescue_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Rescue not found';
                    SET o_old_status = NULL;
                    SET o_report_id = NULL;
                    ROLLBACK;
                ELSEIF v_current_status IN ('Success', 'Failed') THEN
                    SET o_status = 'error';
                    SET o_message = 'Cannot update completed rescue (status: Success/Failed)';
                    SET o_old_status = v_current_status;
                    SET o_report_id = v_report_id;
                    ROLLBACK;
                ELSEIF (p_new_status = 'Success' OR p_new_status = 'Failed')
                       AND (p_remarks IS NULL OR TRIM(p_remarks) = '') THEN
                    SET o_status = 'error';
                    SET o_message = 'Remarks are required when marking rescue as Success or Failed';
                    SET o_old_status = v_current_status;
                    SET o_report_id = v_report_id;
                    ROLLBACK;
                ELSE
                    -- Update rescue status
                    UPDATE rescue
                    SET status = p_new_status,
                        remarks = p_remarks,
                        updated_at = NOW()
                    WHERE id = p_rescue_id;

                    -- Update report status based on rescue status (triggers will also handle this)
                    IF p_new_status = 'In Progress' THEN
                        UPDATE report
                        SET report_status = 'In Progress',
                            updated_at = NOW()
                        WHERE id = v_report_id;
                    ELSEIF p_new_status = 'Success' OR p_new_status = 'Failed' THEN
                        UPDATE report
                        SET report_status = 'Completed',
                            updated_at = NOW()
                        WHERE id = v_report_id;
                    END IF;

                    SET o_old_status = v_current_status;
                    SET o_report_id = v_report_id;
                    SET o_status = 'success';
                    SET o_message = 'Rescue status updated successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_rescue_update_priority
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_rescue_update_priority
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_rescue_update_priority(
                IN p_rescue_id BIGINT,
                IN p_priority VARCHAR(20),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_old_priority VARCHAR(20),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_current_priority VARCHAR(20);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_old_priority = NULL;
                END;

                START TRANSACTION;

                -- Check if rescue exists and get current priority
                SELECT COUNT(*), MAX(priority)
                INTO v_exists, v_current_priority
                FROM rescue
                WHERE id = p_rescue_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Rescue not found';
                    SET o_old_priority = NULL;
                    ROLLBACK;
                ELSEIF p_priority NOT IN ('critical', 'high', 'normal') THEN
                    SET o_status = 'error';
                    SET o_message = 'Invalid priority. Must be: critical, high, or normal';
                    SET o_old_priority = v_current_priority;
                    ROLLBACK;
                ELSE
                    -- Update rescue priority
                    UPDATE rescue
                    SET priority = p_priority,
                        updated_at = NOW()
                    WHERE id = p_rescue_id;

                    SET o_old_priority = v_current_priority;
                    SET o_status = 'success';
                    SET o_message = 'Rescue priority updated successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_rescue_read_by_caretaker
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_rescue_read_by_caretaker
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_rescue_read_by_caretaker(
                IN p_caretaker_id BIGINT,
                IN p_priority VARCHAR(20),
                IN p_status VARCHAR(50),
                IN p_offset INT,
                IN p_limit INT,
                OUT o_total_count INT
            )
            BEGIN
                -- Get total count
                SELECT COUNT(*) INTO o_total_count
                FROM rescue
                WHERE caretakerID = p_caretaker_id
                  AND (p_priority IS NULL OR priority = p_priority)
                  AND (p_status IS NULL OR status = p_status);

                -- Get paginated data (returned as result set)
                SELECT
                    id,
                    status,
                    priority,
                    remarks,
                    reportID,
                    caretakerID,
                    created_at,
                    updated_at
                FROM rescue
                WHERE caretakerID = p_caretaker_id
                  AND (p_priority IS NULL OR priority = p_priority)
                  AND (p_status IS NULL OR status = p_status)
                ORDER BY
                    CASE
                        WHEN priority = 'critical' THEN 1
                        WHEN priority = 'high' THEN 2
                        WHEN priority = 'normal' THEN 3
                        ELSE 4
                    END,
                    created_at DESC
                LIMIT p_limit OFFSET p_offset;
            END
        ");

        // ===========================
        // sp_rescue_get_status_counts
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_rescue_get_status_counts
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_rescue_get_status_counts(
                IN p_caretaker_id BIGINT
            )
            BEGIN
                SELECT
                    status,
                    COUNT(*) as total
                FROM rescue
                WHERE caretakerID = p_caretaker_id
                GROUP BY status;
            END
        ");

        // ===========================
        // sp_rescue_create (optional - for direct rescue creation)
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_rescue_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_rescue_create(
                IN p_report_id BIGINT,
                IN p_caretaker_id BIGINT,
                IN p_priority VARCHAR(20),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_rescue_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_report_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_rescue_id = NULL;
                END;

                START TRANSACTION;

                -- Check if report exists
                SELECT COUNT(*) INTO v_report_exists FROM report WHERE id = p_report_id;

                IF v_report_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Report not found';
                    SET o_rescue_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Insert rescue
                    INSERT INTO rescue (
                        status, priority, remarks, reportID, caretakerID,
                        created_at, updated_at
                    )
                    VALUES (
                        'Scheduled',
                        COALESCE(p_priority, 'normal'),
                        NULL,
                        p_report_id,
                        p_caretaker_id,
                        NOW(),
                        NOW()
                    );

                    SET o_rescue_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Rescue created successfully';
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
        $connection = DB::connection('eilya');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_rescue_assign_caretaker');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_rescue_update_status');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_rescue_update_priority');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_rescue_read_by_caretaker');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_rescue_get_status_counts');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_rescue_create');
    }
};
