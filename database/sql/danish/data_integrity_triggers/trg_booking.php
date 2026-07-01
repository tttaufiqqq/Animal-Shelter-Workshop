<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_booking_cascade_delete;
DROP TRIGGER IF EXISTS trg_booking_prevent_delete_with_adoptions;
DROP TRIGGER IF EXISTS trg_booking_update_timestamp;
DROP TRIGGER IF EXISTS trg_booking_validate_status_transition;
DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_insert;
DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_update;

CREATE TRIGGER trg_booking_cascade_delete
AFTER DELETE ON booking
FOR EACH ROW
BEGIN
    DELETE FROM animal_booking WHERE bookingID = OLD.id;
END;

CREATE TRIGGER trg_booking_prevent_delete_with_adoptions
BEFORE DELETE ON booking
FOR EACH ROW
BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count FROM adoption WHERE bookingID = OLD.id;
    IF v_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete booking with associated adoptions. Please delete adoptions first.';
    END IF;
END;

CREATE TRIGGER trg_booking_update_timestamp
BEFORE UPDATE ON booking
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END;

CREATE TRIGGER trg_booking_validate_status_transition
BEFORE UPDATE ON booking
FOR EACH ROW
BEGIN
    IF OLD.status = 'Completed' AND NEW.status != 'Completed' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot modify a completed booking';
    END IF;
    IF OLD.status = 'Adopted' AND NEW.status != 'Adopted' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot modify an adopted booking';
    END IF;
END;

CREATE TRIGGER trg_booking_validate_appointment_date_insert
BEFORE INSERT ON booking
FOR EACH ROW
BEGIN
    IF NEW.appointment_date < CURDATE() AND NEW.status = 'Pending' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Appointment date cannot be in the past';
    END IF;
END;

CREATE TRIGGER trg_booking_validate_appointment_date_update
BEFORE UPDATE ON booking
FOR EACH ROW
BEGIN
    IF NEW.appointment_date < CURDATE() AND NEW.status = 'Pending' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Appointment date cannot be in the past';
    END IF;
END;
SQL;
