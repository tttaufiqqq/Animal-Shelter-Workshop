<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_report_delete;

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
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_has_rescue = FALSE;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_exists FROM report WHERE id = p_report_id;

    IF v_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Report not found';
        SET o_has_rescue = FALSE;
        ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_rescue_count FROM rescue WHERE reportID = p_report_id;

        IF v_rescue_count > 0 THEN
            SET o_status = 'error';
            SET o_message = 'Cannot delete report with associated rescue. Please delete the rescue first.';
            SET o_has_rescue = TRUE;
            ROLLBACK;
        ELSE
            DELETE FROM report WHERE id = p_report_id;

            SET o_status = 'success';
            SET o_message = 'Report deleted successfully';
            SET o_has_rescue = FALSE;
            COMMIT;
        END IF;
    END IF;
END;
SQL;
