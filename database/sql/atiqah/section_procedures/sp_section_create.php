<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_section_create;

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

    IF p_name IS NULL OR TRIM(p_name) = '' THEN
        SET o_status = 'error'; SET o_message = 'Section name is required'; SET o_section_id = NULL; ROLLBACK;
    ELSEIF p_description IS NULL OR TRIM(p_description) = '' THEN
        SET o_status = 'error'; SET o_message = 'Section description is required'; SET o_section_id = NULL; ROLLBACK;
    ELSE
        INSERT INTO section (name, description, created_at, updated_at)
        VALUES (p_name, p_description, NOW(), NOW());

        SET o_section_id = LAST_INSERT_ID();
        SET o_status = 'success';
        SET o_message = 'Section created successfully';
        COMMIT;
    END IF;
END;
SQL;
