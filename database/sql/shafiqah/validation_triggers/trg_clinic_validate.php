<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_clinic_validate_insert;
DROP TRIGGER IF EXISTS trg_clinic_validate_update;

CREATE TRIGGER trg_clinic_validate_insert
BEFORE INSERT ON clinic
FOR EACH ROW
BEGIN
    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Clinic name is required';
    END IF;

    IF NEW.latitude IS NOT NULL AND (NEW.latitude < -90 OR NEW.latitude > 90) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Latitude must be between -90 and 90';
    END IF;

    IF NEW.longitude IS NOT NULL AND (NEW.longitude < -180 OR NEW.longitude > 180) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Longitude must be between -180 and 180';
    END IF;

    IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
    END IF;
END;

CREATE TRIGGER trg_clinic_validate_update
BEFORE UPDATE ON clinic
FOR EACH ROW
BEGIN
    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Clinic name is required';
    END IF;

    IF NEW.latitude IS NOT NULL AND (NEW.latitude < -90 OR NEW.latitude > 90) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Latitude must be between -90 and 90';
    END IF;

    IF NEW.longitude IS NOT NULL AND (NEW.longitude < -180 OR NEW.longitude > 180) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Longitude must be between -180 and 180';
    END IF;

    IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
    END IF;
END;
SQL;
