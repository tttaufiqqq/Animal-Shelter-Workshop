<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS section_after_insert;
DROP TRIGGER IF EXISTS section_after_update;
DROP TRIGGER IF EXISTS section_after_delete;

CREATE TRIGGER section_after_insert
AFTER INSERT ON section
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, new_values, user_id, user_name, user_email, user_role)
    VALUES ('section', 'INSERT', NEW.id,
        CONCAT('name: ', NEW.name, ', description: ', NEW.description),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER section_after_update
AFTER UPDATE ON section
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, new_values, user_id, user_name, user_email, user_role)
    VALUES ('section', 'UPDATE', NEW.id,
        CONCAT('name: ', OLD.name, ', description: ', OLD.description),
        CONCAT('name: ', NEW.name, ', description: ', NEW.description),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER section_after_delete
AFTER DELETE ON section
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, user_id, user_name, user_email, user_role)
    VALUES ('section', 'DELETE', OLD.id,
        CONCAT('name: ', OLD.name, ', description: ', OLD.description),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;
SQL;
