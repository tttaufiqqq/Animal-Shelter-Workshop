<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_clinic_delete;

CREATE PROCEDURE sp_clinic_delete(
    IN p_clinic_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_vet_count INT;
    DECLARE v_clinic_name VARCHAR(255);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    SELECT name INTO v_clinic_name FROM clinic WHERE id = p_clinic_id;

    IF v_clinic_name IS NULL THEN
        SET o_status = 'error';
        SET o_message = 'Clinic not found';
        ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_vet_count FROM vet WHERE clinicID = p_clinic_id;

        IF v_vet_count > 0 THEN
            SET o_status = 'error';
            SET o_message = CONCAT('Cannot delete clinic with ', v_vet_count, ' associated veterinarians. Please reassign or remove vets first.');
            ROLLBACK;
        ELSE
            DELETE FROM clinic WHERE id = p_clinic_id;

            SET o_status = 'success';
            SET o_message = CONCAT('Clinic "', v_clinic_name, '" deleted successfully');
            COMMIT;
        END IF;
    END IF;
END;
SQL;
