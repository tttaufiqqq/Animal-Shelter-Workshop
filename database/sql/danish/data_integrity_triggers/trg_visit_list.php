<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_visit_list_cascade_delete;
DROP TRIGGER IF EXISTS trg_visit_list_update_timestamp;

CREATE TRIGGER trg_visit_list_cascade_delete
AFTER DELETE ON visit_list
FOR EACH ROW
BEGIN
    DELETE FROM visit_list_animal WHERE listID = OLD.id;
END;

CREATE TRIGGER trg_visit_list_update_timestamp
BEFORE UPDATE ON visit_list
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END;
SQL;
