<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_inventory_delete;

CREATE PROCEDURE sp_inventory_delete(
    IN p_inventory_id BIGINT,
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

    SELECT COUNT(*) INTO v_exists FROM inventory WHERE id = p_inventory_id;

    IF v_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Inventory item not found';
        ROLLBACK;
    ELSE
        DELETE FROM inventory WHERE id = p_inventory_id;

        SET o_status = 'success';
        SET o_message = 'Inventory item deleted successfully';
        COMMIT;
    END IF;
END;
SQL;
