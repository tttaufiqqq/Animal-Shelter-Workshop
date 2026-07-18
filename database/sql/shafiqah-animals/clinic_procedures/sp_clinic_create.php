<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_clinic_create;

CREATE PROCEDURE sp_clinic_create(
    IN p_name VARCHAR(255),
    IN p_address VARCHAR(500),
    IN p_contact_num VARCHAR(30),
    IN p_latitude DECIMAL(10,8),
    IN p_longitude DECIMAL(11,8),
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_clinic_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_clinic_id = NULL;
    END;

    START TRANSACTION;

    INSERT INTO clinic (name, address, contactNum, latitude, longitude, created_at, updated_at)
    VALUES (p_name, p_address, p_contact_num, p_latitude, p_longitude, NOW(), NOW());

    SET o_clinic_id = LAST_INSERT_ID();
    SET o_status = 'success';
    SET o_message = CONCAT('Clinic "', p_name, '" created successfully');

    COMMIT;
END;
SQL;
