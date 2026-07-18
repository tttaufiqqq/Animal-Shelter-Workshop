<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_vet_update;

CREATE PROCEDURE sp_vet_update(
    IN p_vet_id BIGINT,
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_contact_num VARCHAR(30),
    IN p_specialization VARCHAR(255),
    IN p_license_no VARCHAR(50),
    IN p_clinic_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_email_exists INT;
    DECLARE v_current_email VARCHAR(255);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    SELECT email INTO v_current_email FROM vet WHERE id = p_vet_id;

    IF v_current_email IS NULL THEN
        SET o_status = 'error';
        SET o_message = 'Veterinarian not found';
        ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_email_exists
        FROM vet
        WHERE LOWER(email) = LOWER(p_email COLLATE utf8mb4_0900_ai_ci) AND id != p_vet_id;

        IF v_email_exists > 0 THEN
            SET o_status = 'error';
            SET o_message = 'Email already exists';
            ROLLBACK;
        ELSE
            UPDATE vet
            SET
                name = p_name,
                email = p_email,
                contactNum = p_contact_num,
                specialization = p_specialization,
                license_no = p_license_no,
                clinicID = p_clinic_id,
                updated_at = NOW()
            WHERE id = p_vet_id;

            SET o_status = 'success';
            SET o_message = CONCAT('Veterinarian "', p_name, '" updated successfully');
            COMMIT;
        END IF;
    END IF;
END;
SQL;
