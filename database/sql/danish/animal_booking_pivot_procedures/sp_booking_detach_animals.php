<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_detach_animals;

CREATE PROCEDURE sp_booking_detach_animals(
    IN p_booking_id BIGINT,
    IN p_animal_ids TEXT,
    OUT o_detached_count INT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_booking_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_detached_count = 0;
    END;

    SELECT COUNT(*) INTO v_booking_exists FROM booking WHERE id = p_booking_id;
    IF v_booking_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Booking not found'; SET o_detached_count = 0;
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    IF p_animal_ids IS NULL THEN
        DELETE FROM animal_booking WHERE bookingID = p_booking_id;
    ELSE
        DELETE FROM animal_booking
        WHERE bookingID = p_booking_id
          AND FIND_IN_SET(animalID, p_animal_ids) > 0;
    END IF;

    SET o_detached_count = ROW_COUNT();
    SET o_status = 'success';
    SET o_message = CONCAT(o_detached_count, ' animal(s) detached from booking');

    COMMIT;
END;
SQL;
