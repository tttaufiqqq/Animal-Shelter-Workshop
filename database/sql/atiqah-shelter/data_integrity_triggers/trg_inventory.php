<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS inventory_after_insert;
DROP TRIGGER IF EXISTS inventory_after_update;
DROP TRIGGER IF EXISTS inventory_after_delete;
DROP TRIGGER IF EXISTS inventory_before_insert_validate;
DROP TRIGGER IF EXISTS inventory_before_update_validate;

CREATE TRIGGER inventory_before_insert_validate
BEFORE INSERT ON inventory
FOR EACH ROW
BEGIN
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Inventory quantity cannot be negative';
    END IF;
END;

CREATE TRIGGER inventory_before_update_validate
BEFORE UPDATE ON inventory
FOR EACH ROW
BEGIN
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Inventory quantity cannot be negative';
    END IF;
END;

CREATE TRIGGER inventory_after_insert
AFTER INSERT ON inventory
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, new_values, user_id, user_name, user_email, user_role)
    VALUES ('inventory', 'INSERT', NEW.id,
        CONCAT('item_name: ', NEW.item_name, ', slotID: ', NEW.slotID, ', categoryID: ', NEW.categoryID, ', quantity: ', NEW.quantity, ', status: ', NEW.status),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER inventory_after_update
AFTER UPDATE ON inventory
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, new_values, user_id, user_name, user_email, user_role)
    VALUES ('inventory', 'UPDATE', NEW.id,
        CONCAT('item_name: ', OLD.item_name, ', slotID: ', OLD.slotID, ', categoryID: ', OLD.categoryID, ', quantity: ', OLD.quantity, ', status: ', OLD.status),
        CONCAT('item_name: ', NEW.item_name, ', slotID: ', NEW.slotID, ', categoryID: ', NEW.categoryID, ', quantity: ', NEW.quantity, ', status: ', NEW.status),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER inventory_after_delete
AFTER DELETE ON inventory
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, user_id, user_name, user_email, user_role)
    VALUES ('inventory', 'DELETE', OLD.id,
        CONCAT('item_name: ', OLD.item_name, ', slotID: ', OLD.slotID, ', categoryID: ', OLD.categoryID, ', quantity: ', OLD.quantity, ', status: ', OLD.status),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;
SQL;
