<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_inventory_update;

CREATE PROCEDURE sp_inventory_update(
    IN p_inventory_id BIGINT,
    IN p_item_name VARCHAR(255),
    IN p_category_id BIGINT,
    IN p_quantity INT,
    IN p_weight DECIMAL(10,2),
    IN p_brand VARCHAR(255),
    IN p_status VARCHAR(50),
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_category_exists INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_exists FROM inventory WHERE id = p_inventory_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Inventory item not found'; ROLLBACK;
    ELSEIF p_item_name IS NULL OR TRIM(p_item_name) = '' THEN
        SET o_status = 'error'; SET o_message = 'Item name is required'; ROLLBACK;
    ELSEIF p_category_id IS NULL THEN
        SET o_status = 'error'; SET o_message = 'Category ID is required'; ROLLBACK;
    ELSEIF p_quantity IS NULL OR p_quantity < 0 THEN
        SET o_status = 'error'; SET o_message = 'Quantity must be 0 or greater'; ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_category_exists FROM category WHERE id = p_category_id;

        IF v_category_exists = 0 THEN
            SET o_status = 'error'; SET o_message = 'The selected category does not exist'; ROLLBACK;
        ELSE
            UPDATE inventory
            SET item_name = p_item_name, categoryID = p_category_id, quantity = p_quantity,
                weight = p_weight, brand = p_brand, status = p_status, updated_at = NOW()
            WHERE id = p_inventory_id;

            SET o_status = 'success';
            SET o_message = 'Inventory item updated successfully';
            COMMIT;
        END IF;
    END IF;
END;
SQL;
