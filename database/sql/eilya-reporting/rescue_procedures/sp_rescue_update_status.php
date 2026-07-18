<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_rescue_update_status;

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
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_old_status = NULL;
        SET o_report_id = NULL;
    END;

    START TRANSACTION;

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
        UPDATE rescue SET status = p_new_status, remarks = p_remarks, updated_at = NOW()
        WHERE id = p_rescue_id;

        IF p_new_status = 'In Progress' THEN
            UPDATE report SET report_status = 'In Progress', updated_at = NOW() WHERE id = v_report_id;
        ELSEIF p_new_status = 'Success' OR p_new_status = 'Failed' THEN
            UPDATE report SET report_status = 'Completed', updated_at = NOW() WHERE id = v_report_id;
        END IF;

        SET o_old_status = v_current_status;
        SET o_report_id = v_report_id;
        SET o_status = 'success';
        SET o_message = 'Rescue status updated successfully';
        COMMIT;
    END IF;
END;
SQL;
