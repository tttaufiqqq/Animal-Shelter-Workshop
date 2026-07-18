<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_rescue_create;

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
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_rescue_id = NULL;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_report_exists FROM report WHERE id = p_report_id;

    IF v_report_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Report not found';
        SET o_rescue_id = NULL;
        ROLLBACK;
    ELSE
        INSERT INTO rescue (status, priority, remarks, reportID, caretakerID, created_at, updated_at)
        VALUES ('Scheduled', COALESCE(p_priority, 'normal'), NULL, p_report_id, p_caretaker_id, NOW(), NOW());

        SET o_rescue_id = LAST_INSERT_ID();
        SET o_status = 'success';
        SET o_message = 'Rescue created successfully';
        COMMIT;
    END IF;
END;
SQL;
