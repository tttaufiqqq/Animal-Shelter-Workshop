<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_animal_assign_slot;

CREATE PROCEDURE sp_animal_assign_slot(
    IN p_animal_id BIGINT,
    IN p_slot_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_previous_slot_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_previous_slot_id = NULL;
    END;

    START TRANSACTION;

    SELECT slotID INTO o_previous_slot_id FROM animal WHERE id = p_animal_id;

    IF o_previous_slot_id IS NULL AND NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
        SET o_status = 'error';
        SET o_message = 'Animal not found';
        ROLLBACK;
    ELSE
        UPDATE animal
        SET slotID = p_slot_id, updated_at = NOW()
        WHERE id = p_animal_id;

        SET o_status = 'success';
        SET o_message = 'Slot assigned successfully';
        COMMIT;
    END IF;
END;
SQL;
