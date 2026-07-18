<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_rescue_update_priority;

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
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_old_priority = NULL;
    END;

    START TRANSACTION;

    SELECT COUNT(*), MAX(priority)
    INTO v_exists, v_current_priority
    FROM rescue WHERE id = p_rescue_id;

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
        UPDATE rescue SET priority = p_priority, updated_at = NOW() WHERE id = p_rescue_id;

        SET o_old_priority = v_current_priority;
        SET o_status = 'success';
        SET o_message = 'Rescue priority updated successfully';
        COMMIT;
    END IF;
END;
SQL;
