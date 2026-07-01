<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_create;

CREATE PROCEDURE sp_booking_create(
    IN p_user_id BIGINT,
    IN p_appointment_date DATE,
    IN p_appointment_time TIME,
    IN p_status VARCHAR(50),
    OUT o_booking_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_booking_id = NULL;
    END;

    SET p_status = IFNULL(p_status, 'Pending');

    IF p_user_id IS NULL THEN
        SET o_status = 'error'; SET o_message = 'User ID is required'; SET o_booking_id = NULL;
        LEAVE proc_exit;
    END IF;

    IF p_appointment_date IS NULL OR p_appointment_time IS NULL THEN
        SET o_status = 'error'; SET o_message = 'Appointment date and time are required'; SET o_booking_id = NULL;
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    INSERT INTO booking (userID, appointment_date, appointment_time, status, created_at, updated_at)
    VALUES (p_user_id, p_appointment_date, p_appointment_time, p_status, NOW(), NOW());

    SET o_booking_id = LAST_INSERT_ID();
    SET o_status = 'success';
    SET o_message = 'Booking created successfully';

    COMMIT;
END;
SQL;
