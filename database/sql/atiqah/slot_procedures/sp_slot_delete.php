<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_slot_delete;

CREATE PROCEDURE sp_slot_delete(
    IN p_slot_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_has_animals BOOLEAN,
    OUT o_animal_count INT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_inventory_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_has_animals = FALSE;
        SET o_animal_count = 0;
    END;

    START TRANSACTION;

    SET o_has_animals = FALSE;
    SET o_animal_count = 0;

    SELECT COUNT(*) INTO v_exists FROM slot WHERE id = p_slot_id;

    IF v_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Slot not found';
        ROLLBACK;
    ELSE
        -- Animal count check is at application layer (animals are in a different database)
        SELECT COUNT(*) INTO v_inventory_count FROM inventory WHERE slotID = p_slot_id;

        IF v_inventory_count > 0 THEN
            SET o_status = 'error';
            SET o_message = CONCAT('Cannot delete slot with existing inventory items. Please reassign or delete ', v_inventory_count, ' inventory item(s) first.');
            ROLLBACK;
        ELSE
            DELETE FROM slot WHERE id = p_slot_id;

            SET o_status = 'success';
            SET o_message = 'Slot deleted successfully';
            COMMIT;
        END IF;
    END IF;
END;
SQL;
