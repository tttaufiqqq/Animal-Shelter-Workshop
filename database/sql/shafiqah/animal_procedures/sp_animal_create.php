<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_animal_create;

CREATE PROCEDURE sp_animal_create(
    IN p_name VARCHAR(255),
    IN p_species VARCHAR(255),
    IN p_health_details TEXT,
    IN p_age VARCHAR(255),
    IN p_gender ENUM('Male', 'Female', 'Unknown'),
    IN p_weight DECIMAL(8,2),
    IN p_adoption_status VARCHAR(255),
    IN p_rescue_id BIGINT,
    IN p_slot_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_animal_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_animal_id = NULL;
    END;

    START TRANSACTION;

    INSERT INTO animal (
        name, species, health_details, age, gender, weight,
        adoption_status, rescueID, slotID, created_at, updated_at
    )
    VALUES (
        p_name, p_species, p_health_details, p_age, p_gender, p_weight,
        COALESCE(p_adoption_status, 'Not Adopted'), p_rescue_id, p_slot_id, NOW(), NOW()
    );

    SET o_animal_id = LAST_INSERT_ID();
    SET o_status = 'success';
    SET o_message = CONCAT('Animal "', p_name, '" created successfully');

    COMMIT;
END;
SQL;
