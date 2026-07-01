<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_slot_read;

CREATE PROCEDURE sp_slot_read(
    IN p_slot_id BIGINT
)
BEGIN
    SELECT id, name, sectionID, capacity, status, created_at, updated_at
    FROM slot
    WHERE id = p_slot_id;
END;
SQL;
