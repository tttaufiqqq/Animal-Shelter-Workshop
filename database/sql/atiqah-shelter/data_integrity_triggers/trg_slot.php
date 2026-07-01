<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS slot_after_insert;
DROP TRIGGER IF EXISTS slot_after_update;
DROP TRIGGER IF EXISTS slot_after_delete;
DROP TRIGGER IF EXISTS slot_before_insert_validate;
DROP TRIGGER IF EXISTS slot_before_update_validate;

CREATE TRIGGER slot_before_insert_validate
BEFORE INSERT ON slot
FOR EACH ROW
BEGIN
    IF NEW.capacity < 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Slot capacity must be at least 1';
    END IF;
END;

CREATE TRIGGER slot_before_update_validate
BEFORE UPDATE ON slot
FOR EACH ROW
BEGIN
    IF NEW.capacity < 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Slot capacity must be at least 1';
    END IF;
END;

CREATE TRIGGER slot_after_insert
AFTER INSERT ON slot
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, new_values, user_id, user_name, user_email, user_role)
    VALUES ('slot', 'INSERT', NEW.id,
        CONCAT('name: ', NEW.name, ', sectionID: ', NEW.sectionID, ', capacity: ', NEW.capacity, ', status: ', NEW.status),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER slot_after_update
AFTER UPDATE ON slot
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, new_values, user_id, user_name, user_email, user_role)
    VALUES ('slot', 'UPDATE', NEW.id,
        CONCAT('name: ', OLD.name, ', sectionID: ', OLD.sectionID, ', capacity: ', OLD.capacity, ', status: ', OLD.status),
        CONCAT('name: ', NEW.name, ', sectionID: ', NEW.sectionID, ', capacity: ', NEW.capacity, ', status: ', NEW.status),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER slot_after_delete
AFTER DELETE ON slot
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, user_id, user_name, user_email, user_role)
    VALUES ('slot', 'DELETE', OLD.id,
        CONCAT('name: ', OLD.name, ', sectionID: ', OLD.sectionID, ', capacity: ', OLD.capacity, ', status: ', OLD.status),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;
SQL;
