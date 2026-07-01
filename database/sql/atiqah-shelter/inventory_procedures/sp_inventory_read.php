<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_inventory_read;

CREATE PROCEDURE sp_inventory_read(
    IN p_inventory_id BIGINT
)
BEGIN
    SELECT id, slotID, item_name, categoryID, quantity, weight, brand, status, created_at, updated_at
    FROM inventory
    WHERE id = p_inventory_id;
END;
SQL;
