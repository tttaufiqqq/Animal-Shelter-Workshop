<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_visit_list_get_animals;

CREATE PROCEDURE sp_visit_list_get_animals(
    IN p_list_id BIGINT
)
BEGIN
    SELECT animalID, created_at, updated_at
    FROM visit_list_animal
    WHERE listID = p_list_id
    ORDER BY created_at DESC;
END;
SQL;
