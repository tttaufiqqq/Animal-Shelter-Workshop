<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_section_read;

CREATE PROCEDURE sp_section_read(
    IN p_section_id BIGINT
)
BEGIN
    SELECT id, name, description, created_at, updated_at
    FROM section
    WHERE id = p_section_id;
END;
SQL;
