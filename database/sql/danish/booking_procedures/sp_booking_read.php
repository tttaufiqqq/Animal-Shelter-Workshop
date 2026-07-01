<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_read;

CREATE PROCEDURE sp_booking_read(
    IN p_booking_id BIGINT
)
BEGIN
    SELECT id, userID, appointment_date, appointment_time, status, remarks, created_at, updated_at
    FROM booking
    WHERE id = p_booking_id;
END;
SQL;
