<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_cancel;

CREATE PROCEDURE sp_booking_cancel(
    IN p_booking_id BIGINT,
    IN p_user_id BIGINT,
    OUT o_old_status VARCHAR(50),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_booking_user_id BIGINT DEFAULT NULL;
    DECLARE v_current_status VARCHAR(50) DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_old_status = NULL;
    END;

    SELECT COUNT(*), status, userID
    INTO v_exists, v_current_status, v_booking_user_id
    FROM booking
    WHERE id = p_booking_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Booking not found'; SET o_old_status = NULL;
        LEAVE proc_exit;
    END IF;

    IF v_booking_user_id != p_user_id THEN
        SET o_status = 'error'; SET o_message = 'Unauthorized: This booking belongs to another user';
        SET o_old_status = v_current_status;
        LEAVE proc_exit;
    END IF;

    IF v_current_status NOT IN ('Pending', 'Confirmed') THEN
        SET o_status = 'error'; SET o_message = CONCAT('Cannot cancel booking with status: ', v_current_status);
        SET o_old_status = v_current_status;
        LEAVE proc_exit;
    END IF;

    SET o_old_status = v_current_status;

    START TRANSACTION;

    UPDATE booking SET status = 'Cancelled', updated_at = NOW() WHERE id = p_booking_id;

    SET o_status = 'success';
    SET o_message = 'Booking cancelled successfully';

    COMMIT;
END;
SQL;
