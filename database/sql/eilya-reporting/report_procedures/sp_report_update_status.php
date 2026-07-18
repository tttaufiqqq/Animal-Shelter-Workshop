<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_report_update_status;

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
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_exists FROM report WHERE id = p_report_id;

    IF v_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Report not found';
        ROLLBACK;
    ELSE
        UPDATE report SET report_status = p_new_status, updated_at = NOW() WHERE id = p_report_id;

        SET o_status = 'success';
        SET o_message = 'Report status updated successfully';
        COMMIT;
    END IF;
END;
SQL;
