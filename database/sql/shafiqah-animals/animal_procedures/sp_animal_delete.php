<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_animal_delete;

CREATE PROCEDURE sp_animal_delete(
    IN p_animal_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_animal_name VARCHAR(255),
    OUT o_slot_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_animal_name = NULL;
        SET o_slot_id = NULL;
    END;

    START TRANSACTION;

    SELECT name, slotID INTO o_animal_name, o_slot_id
    FROM animal
    WHERE id = p_animal_id;

    IF o_animal_name IS NULL THEN
        SET o_status = 'error';
        SET o_message = 'Animal not found';
        ROLLBACK;
    ELSE
        DELETE FROM animal WHERE id = p_animal_id;

        SET o_status = 'success';
        SET o_message = CONCAT('Animal "', o_animal_name, '" deleted successfully');
        COMMIT;
    END IF;
END;
SQL;
