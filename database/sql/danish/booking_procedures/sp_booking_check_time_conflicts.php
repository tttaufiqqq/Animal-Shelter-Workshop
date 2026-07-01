<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_check_time_conflicts;

CREATE PROCEDURE sp_booking_check_time_conflicts(
    IN p_appointment_date DATE,
    IN p_appointment_time TIME,
    IN p_animal_ids TEXT,
    IN p_exclude_booking_id BIGINT
)
BEGIN
    SELECT DISTINCT ab.animalID
    FROM booking b
    INNER JOIN animal_booking ab ON b.id = ab.bookingID
    WHERE b.appointment_date = p_appointment_date
      AND b.appointment_time = p_appointment_time
      AND b.status IN ('Pending', 'Confirmed')
      AND (p_exclude_booking_id IS NULL OR b.id != p_exclude_booking_id)
      AND FIND_IN_SET(ab.animalID, p_animal_ids) > 0;
END;
SQL;
