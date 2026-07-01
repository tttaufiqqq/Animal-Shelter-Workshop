<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_rescue_assign_caretaker;

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

    SELECT COUNT(*) INTO v_report_exists FROM report WHERE id = p_report_id;

    IF v_report_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Report not found';
        SET o_rescue_id = NULL;
        SET o_is_reassignment = FALSE;
        SET o_old_caretaker_id = NULL;
        ROLLBACK;
    ELSE
        SELECT id, caretakerID
        INTO v_existing_rescue_id, v_current_caretaker_id
        FROM rescue
        WHERE reportID = p_report_id
        LIMIT 1;

        IF v_existing_rescue_id IS NOT NULL THEN
            IF v_current_caretaker_id = p_caretaker_id THEN
                SET o_status = 'error';
                SET o_message = 'This report is already assigned to the same caretaker';
                SET o_rescue_id = v_existing_rescue_id;
                SET o_is_reassignment = FALSE;
                SET o_old_caretaker_id = v_current_caretaker_id;
                ROLLBACK;
            ELSE
                UPDATE rescue
                SET caretakerID = p_caretaker_id, updated_at = NOW()
                WHERE id = v_existing_rescue_id;

                SET o_rescue_id = v_existing_rescue_id;
                SET o_is_reassignment = TRUE;
                SET o_old_caretaker_id = v_current_caretaker_id;
                SET o_status = 'success';
                SET o_message = 'Caretaker reassigned successfully';
                COMMIT;
            END IF;
        ELSE
            INSERT INTO rescue (status, priority, remarks, reportID, caretakerID, created_at, updated_at)
            VALUES ('Scheduled', 'normal', NULL, p_report_id, p_caretaker_id, NOW(), NOW());

            SET o_rescue_id = LAST_INSERT_ID();
            SET o_is_reassignment = FALSE;
            SET o_old_caretaker_id = NULL;
            SET o_status = 'success';
            SET o_message = 'Caretaker assigned successfully';

            UPDATE report SET report_status = 'Assigned', updated_at = NOW() WHERE id = p_report_id;

            COMMIT;
        END IF;
    END IF;
END;
SQL;
