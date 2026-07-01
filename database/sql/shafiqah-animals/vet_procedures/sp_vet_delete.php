<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_vet_delete;

CREATE PROCEDURE sp_vet_delete(
    IN p_vet_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_medical_count INT;
    DECLARE v_vaccination_count INT;
    DECLARE v_vet_name VARCHAR(255);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    SELECT name INTO v_vet_name FROM vet WHERE id = p_vet_id;

    IF v_vet_name IS NULL THEN
        SET o_status = 'error';
        SET o_message = 'Veterinarian not found';
        ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_medical_count FROM medical WHERE vetID = p_vet_id;
        SELECT COUNT(*) INTO v_vaccination_count FROM vaccination WHERE vetID = p_vet_id;

        IF v_medical_count > 0 OR v_vaccination_count > 0 THEN
            SET o_status = 'error';
            SET o_message = CONCAT('Cannot delete veterinarian with associated medical records (', v_medical_count, ') or vaccination records (', v_vaccination_count, '). Please reassign these records first.');
            ROLLBACK;
        ELSE
            DELETE FROM vet WHERE id = p_vet_id;

            SET o_status = 'success';
            SET o_message = CONCAT('Veterinarian "', v_vet_name, '" deleted successfully');
            COMMIT;
        END IF;
    END IF;
END;
SQL;
