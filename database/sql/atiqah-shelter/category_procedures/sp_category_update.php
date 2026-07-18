<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_category_update;

CREATE PROCEDURE sp_category_update(
    IN p_category_id BIGINT,
    IN p_main VARCHAR(255),
    IN p_sub VARCHAR(255),
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

    SELECT COUNT(*) INTO v_exists FROM category WHERE id = p_category_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Category not found'; ROLLBACK;
    ELSEIF p_main IS NULL OR TRIM(p_main) = '' THEN
        SET o_status = 'error'; SET o_message = 'Main category is required'; ROLLBACK;
    ELSEIF p_sub IS NULL OR TRIM(p_sub) = '' THEN
        SET o_status = 'error'; SET o_message = 'Sub category is required'; ROLLBACK;
    ELSE
        UPDATE category SET main = p_main, sub = p_sub, updated_at = NOW() WHERE id = p_category_id;

        SET o_status = 'success';
        SET o_message = 'Category updated successfully';
        COMMIT;
    END IF;
END;
SQL;
