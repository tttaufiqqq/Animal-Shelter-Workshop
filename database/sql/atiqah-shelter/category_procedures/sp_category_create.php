<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_category_create;

CREATE PROCEDURE sp_category_create(
    IN p_main VARCHAR(255),
    IN p_sub VARCHAR(255),
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_category_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_category_id = NULL;
    END;

    START TRANSACTION;

    IF p_main IS NULL OR TRIM(p_main) = '' THEN
        SET o_status = 'error'; SET o_message = 'Main category is required'; SET o_category_id = NULL; ROLLBACK;
    ELSEIF p_sub IS NULL OR TRIM(p_sub) = '' THEN
        SET o_status = 'error'; SET o_message = 'Sub category is required'; SET o_category_id = NULL; ROLLBACK;
    ELSE
        INSERT INTO category (main, sub, created_at, updated_at) VALUES (p_main, p_sub, NOW(), NOW());

        SET o_category_id = LAST_INSERT_ID();
        SET o_status = 'success';
        SET o_message = 'Category created successfully';
        COMMIT;
    END IF;
END;
SQL;
