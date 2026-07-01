<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_update_status;

CREATE PROCEDURE sp_booking_update_status(
    IN p_booking_id BIGINT,
    IN p_new_status VARCHAR(50),
    IN p_user_id BIGINT,
    OUT o_old_status VARCHAR(50),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_booking_user_id BIGINT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_old_status = NULL;
    END;

    SELECT COUNT(*), status, userID
    INTO v_exists, o_old_status, v_booking_user_id
    FROM booking
    WHERE id = p_booking_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Booking not found'; SET o_old_status = NULL;
        LEAVE proc_exit;
    END IF;

    IF p_user_id IS NOT NULL AND v_booking_user_id != p_user_id THEN
        SET o_status = 'error'; SET o_message = 'Unauthorized: This booking belongs to another user';
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    UPDATE booking SET status = p_new_status, updated_at = NOW() WHERE id = p_booking_id;

    SET o_status = 'success';
    SET o_message = 'Booking status updated successfully';

    COMMIT;
END;
SQL;
