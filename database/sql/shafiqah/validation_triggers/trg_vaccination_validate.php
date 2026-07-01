<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_vaccination_validate_insert;
DROP TRIGGER IF EXISTS trg_vaccination_validate_update;

CREATE TRIGGER trg_vaccination_validate_insert
BEFORE INSERT ON vaccination
FOR EACH ROW
BEGIN
    IF NEW.animalID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for vaccination record';
    END IF;

    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination name is required';
    END IF;

    IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination costs cannot be negative';
    END IF;

    IF NEW.next_due_date IS NOT NULL AND NEW.next_due_date < CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Next due date cannot be in the past';
    END IF;
END;

CREATE TRIGGER trg_vaccination_validate_update
BEFORE UPDATE ON vaccination
FOR EACH ROW
BEGIN
    IF NEW.animalID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for vaccination record';
    END IF;

    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination name is required';
    END IF;

    IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vaccination costs cannot be negative';
    END IF;
END;
SQL;
