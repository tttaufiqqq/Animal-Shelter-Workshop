<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_get_animal_count;

CREATE PROCEDURE sp_booking_get_animal_count(
    IN p_booking_id BIGINT,
    OUT o_count INT
)
BEGIN
    SELECT COUNT(*) INTO o_count FROM animal_booking WHERE bookingID = p_booking_id;
END;
SQL;
