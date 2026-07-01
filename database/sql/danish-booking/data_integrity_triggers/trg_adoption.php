<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_adoption_update_timestamp;

CREATE TRIGGER trg_adoption_update_timestamp
BEFORE UPDATE ON adoption
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END;
SQL;
