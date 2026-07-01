<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_category_read;

CREATE PROCEDURE sp_category_read(
    IN p_category_id BIGINT
)
BEGIN
    SELECT id, main, sub, created_at, updated_at FROM category WHERE id = p_category_id;
END;
SQL;
