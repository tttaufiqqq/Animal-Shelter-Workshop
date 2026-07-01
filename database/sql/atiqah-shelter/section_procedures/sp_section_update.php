<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_section_update;

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

    SELECT COUNT(*) INTO v_exists FROM section WHERE id = p_section_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Section not found'; ROLLBACK;
    ELSEIF p_name IS NULL OR TRIM(p_name) = '' THEN
        SET o_status = 'error'; SET o_message = 'Section name is required'; ROLLBACK;
    ELSEIF p_description IS NULL OR TRIM(p_description) = '' THEN
        SET o_status = 'error'; SET o_message = 'Section description is required'; ROLLBACK;
    ELSE
        UPDATE section
        SET name = p_name, description = p_description, updated_at = NOW()
        WHERE id = p_section_id;

        SET o_status = 'success';
        SET o_message = 'Section updated successfully';
        COMMIT;
    END IF;
END;
SQL;
