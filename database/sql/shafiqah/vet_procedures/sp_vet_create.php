<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_vet_create;

CREATE PROCEDURE sp_vet_create(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_contact_num VARCHAR(30),
    IN p_specialization VARCHAR(255),
    IN p_license_no VARCHAR(50),
    IN p_clinic_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_vet_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_email_exists INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_vet_id = NULL;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_email_exists
    FROM vet
    WHERE LOWER(email) = LOWER(p_email COLLATE utf8mb4_0900_ai_ci);

    IF v_email_exists > 0 THEN
        SET o_status = 'error';
        SET o_message = 'Email already exists';
        SET o_vet_id = NULL;
        ROLLBACK;
    ELSE
        INSERT INTO vet (name, email, contactNum, specialization, license_no, clinicID, created_at, updated_at)
        VALUES (p_name, p_email, p_contact_num, p_specialization, p_license_no, p_clinic_id, NOW(), NOW());

        SET o_vet_id = LAST_INSERT_ID();
        SET o_status = 'success';
        SET o_message = CONCAT('Veterinarian "', p_name, '" created successfully');
        COMMIT;
    END IF;
END;
SQL;
