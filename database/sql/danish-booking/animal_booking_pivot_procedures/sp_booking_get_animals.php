<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_get_animals;

CREATE PROCEDURE sp_booking_get_animals(
    IN p_booking_id BIGINT
)
BEGIN
    SELECT animalID, remarks, created_at, updated_at
    FROM animal_booking
    WHERE bookingID = p_booking_id
    ORDER BY created_at;
END;
SQL;
