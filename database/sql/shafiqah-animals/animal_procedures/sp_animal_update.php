<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_animal_update;

CREATE PROCEDURE sp_animal_update(
    IN p_animal_id BIGINT,
    IN p_name VARCHAR(255),
    IN p_species VARCHAR(255),
    IN p_health_details TEXT,
    IN p_age VARCHAR(255),
    IN p_gender ENUM('Male', 'Female', 'Unknown'),
    IN p_weight DECIMAL(8,2),
    IN p_slot_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    IF NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
        SET o_status = 'error';
        SET o_message = 'Animal not found';
        ROLLBACK;
    ELSE
        UPDATE animal
        SET
            name = p_name,
            species = p_species,
            health_details = p_health_details,
            age = p_age,
            gender = p_gender,
            weight = p_weight,
            slotID = p_slot_id,
            updated_at = NOW()
        WHERE id = p_animal_id;

        SET o_status = 'success';
        SET o_message = CONCAT('Animal "', p_name, '" updated successfully');
        COMMIT;
    END IF;
END;
SQL;
