<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_category_delete;

CREATE PROCEDURE sp_category_delete(
    IN p_category_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_has_inventories BOOLEAN,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_inventory_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_has_inventories = FALSE;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_exists FROM category WHERE id = p_category_id;

    IF v_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Category not found';
        SET o_has_inventories = FALSE;
        ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_inventory_count FROM inventory WHERE categoryID = p_category_id;

        IF v_inventory_count > 0 THEN
            SET o_status = 'error';
            SET o_message = 'Cannot delete category with existing inventory items. Please delete or reassign the inventory items first.';
            SET o_has_inventories = TRUE;
            ROLLBACK;
        ELSE
            DELETE FROM category WHERE id = p_category_id;

            SET o_status = 'success';
            SET o_message = 'Category deleted successfully';
            SET o_has_inventories = FALSE;
            COMMIT;
        END IF;
    END IF;
END;
SQL;
