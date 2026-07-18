<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_clinic_update;

CREATE PROCEDURE sp_clinic_update(
    IN p_clinic_id BIGINT,
    IN p_name VARCHAR(255),
    IN p_address VARCHAR(500),
    IN p_contact_num VARCHAR(30),
    IN p_latitude DECIMAL(10,8),
    IN p_longitude DECIMAL(11,8),
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

    IF NOT EXISTS (SELECT 1 FROM clinic WHERE id = p_clinic_id) THEN
        SET o_status = 'error';
        SET o_message = 'Clinic not found';
        ROLLBACK;
    ELSE
        UPDATE clinic
        SET
            name = p_name,
            address = p_address,
            contactNum = p_contact_num,
            latitude = p_latitude,
            longitude = p_longitude,
            updated_at = NOW()
        WHERE id = p_clinic_id;

        SET o_status = 'success';
        SET o_message = CONCAT('Clinic "', p_name, '" updated successfully');
        COMMIT;
    END IF;
END;
SQL;
