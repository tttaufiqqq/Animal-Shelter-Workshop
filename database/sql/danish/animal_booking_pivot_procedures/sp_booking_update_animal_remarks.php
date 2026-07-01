<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_update_animal_remarks;

CREATE PROCEDURE sp_booking_update_animal_remarks(
    IN p_booking_id BIGINT,
    IN p_animal_id BIGINT,
    IN p_remarks VARCHAR(500),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
    END;

    SELECT COUNT(*) INTO v_exists
    FROM animal_booking
    WHERE bookingID = p_booking_id AND animalID = p_animal_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Animal not found in this booking';
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    UPDATE animal_booking
    SET remarks = p_remarks, updated_at = NOW()
    WHERE bookingID = p_booking_id AND animalID = p_animal_id;

    SET o_status = 'success';
    SET o_message = 'Remarks updated successfully';

    COMMIT;
END;
SQL;
