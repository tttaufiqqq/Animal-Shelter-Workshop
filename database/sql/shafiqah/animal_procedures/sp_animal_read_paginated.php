<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_animal_read_paginated;

CREATE PROCEDURE sp_animal_read_paginated(
    IN p_search VARCHAR(255),
    IN p_species VARCHAR(255),
    IN p_health_details VARCHAR(255),
    IN p_adoption_status VARCHAR(255),
    IN p_gender VARCHAR(20),
    IN p_rescue_ids TEXT,
    IN p_offset INT,
    IN p_limit INT
)
BEGIN
    SELECT
        id, name, species, health_details, age, gender, weight,
        adoption_status, rescueID, slotID, created_at, updated_at
    FROM animal
    WHERE
        (p_search IS NULL OR LOWER(name) LIKE CONCAT('%', LOWER(p_search), '%'))
        AND (p_species IS NULL OR LOWER(species) LIKE CONCAT('%', LOWER(p_species), '%'))
        AND (p_health_details IS NULL OR health_details = p_health_details)
        AND (p_adoption_status IS NULL OR adoption_status = p_adoption_status)
        AND (p_gender IS NULL OR gender = p_gender)
        AND (p_rescue_ids IS NULL OR FIND_IN_SET(rescueID, p_rescue_ids) > 0)
    ORDER BY created_at DESC
    LIMIT p_limit OFFSET p_offset;

    SELECT COUNT(*) as total_count
    FROM animal
    WHERE
        (p_search IS NULL OR LOWER(name) LIKE CONCAT('%', LOWER(p_search), '%'))
        AND (p_species IS NULL OR LOWER(species) LIKE CONCAT('%', LOWER(p_species), '%'))
        AND (p_health_details IS NULL OR health_details = p_health_details)
        AND (p_adoption_status IS NULL OR adoption_status = p_adoption_status)
        AND (p_gender IS NULL OR gender = p_gender)
        AND (p_rescue_ids IS NULL OR FIND_IN_SET(rescueID, p_rescue_ids) > 0);
END;
SQL;
