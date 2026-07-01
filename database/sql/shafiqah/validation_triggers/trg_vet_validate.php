<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_vet_validate_insert;
DROP TRIGGER IF EXISTS trg_vet_validate_update;

CREATE TRIGGER trg_vet_validate_insert
BEFORE INSERT ON vet
FOR EACH ROW
BEGIN
    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vet name is required';
    END IF;

    IF NEW.email IS NOT NULL AND NEW.email != '' AND NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format for vet';
    END IF;

    IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
    END IF;

    IF NEW.license_no IS NOT NULL AND NEW.license_no != '' AND LENGTH(NEW.license_no) < 3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'License number must be at least 3 characters';
    END IF;
END;

CREATE TRIGGER trg_vet_validate_update
BEFORE UPDATE ON vet
FOR EACH ROW
BEGIN
    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vet name is required';
    END IF;

    IF NEW.email IS NOT NULL AND NEW.email != '' AND NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format for vet';
    END IF;

    IF NEW.contactNum IS NOT NULL AND NEW.contactNum != '' AND LENGTH(NEW.contactNum) < 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Contact number must be at least 10 characters';
    END IF;

    IF NEW.license_no IS NOT NULL AND NEW.license_no != '' AND LENGTH(NEW.license_no) < 3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'License number must be at least 3 characters';
    END IF;
END;
SQL;
