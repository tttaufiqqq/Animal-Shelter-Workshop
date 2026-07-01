<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_adoption_get_by_booking;

CREATE PROCEDURE sp_adoption_get_by_booking(
    IN p_booking_id BIGINT
)
BEGIN
    SELECT id, bookingID, transactionID, animalID, fee, remarks, created_at, updated_at
    FROM adoption
    WHERE bookingID = p_booking_id
    ORDER BY created_at DESC;
END;
SQL;
