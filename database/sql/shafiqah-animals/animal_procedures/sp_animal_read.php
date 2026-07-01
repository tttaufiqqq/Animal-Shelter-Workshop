<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_animal_read;

CREATE PROCEDURE sp_animal_read(
    IN p_animal_id BIGINT
)
BEGIN
    SELECT
        id, name, species, health_details, age, gender, weight,
        adoption_status, rescueID, slotID, created_at, updated_at
    FROM animal
    WHERE id = p_animal_id;
END;
SQL;
