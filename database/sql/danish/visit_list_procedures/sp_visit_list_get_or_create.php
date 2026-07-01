<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_visit_list_get_or_create;

CREATE PROCEDURE sp_visit_list_get_or_create(
    IN p_user_id BIGINT,
    OUT o_list_id BIGINT,
    OUT o_is_new TINYINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_list_id = NULL;
        SET o_is_new = 0;
    END;

    SELECT id INTO o_list_id FROM visit_list WHERE userID = p_user_id LIMIT 1;

    IF o_list_id IS NULL THEN
        START TRANSACTION;

        INSERT INTO visit_list (userID, created_at, updated_at) VALUES (p_user_id, NOW(), NOW());

        SET o_list_id = LAST_INSERT_ID();
        SET o_is_new = 1;
        SET o_message = 'Visit list created successfully';

        COMMIT;
    ELSE
        SET o_is_new = 0;
        SET o_message = 'Visit list already exists';
    END IF;

    SET o_status = 'success';
END;
SQL;
