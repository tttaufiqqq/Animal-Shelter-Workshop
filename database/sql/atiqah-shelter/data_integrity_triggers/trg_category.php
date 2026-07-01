<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS category_after_insert;
DROP TRIGGER IF EXISTS category_after_update;
DROP TRIGGER IF EXISTS category_after_delete;

CREATE TRIGGER category_after_insert
AFTER INSERT ON category
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, new_values, user_id, user_name, user_email, user_role)
    VALUES ('category', 'INSERT', NEW.id,
        CONCAT('main: ', NEW.main, ', sub: ', NEW.sub),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER category_after_update
AFTER UPDATE ON category
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, new_values, user_id, user_name, user_email, user_role)
    VALUES ('category', 'UPDATE', NEW.id,
        CONCAT('main: ', OLD.main, ', sub: ', OLD.sub),
        CONCAT('main: ', NEW.main, ', sub: ', NEW.sub),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;

CREATE TRIGGER category_after_delete
AFTER DELETE ON category
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, operation, record_id, old_values, user_id, user_name, user_email, user_role)
    VALUES ('category', 'DELETE', OLD.id,
        CONCAT('main: ', OLD.main, ', sub: ', OLD.sub),
        @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role);
END;
SQL;
