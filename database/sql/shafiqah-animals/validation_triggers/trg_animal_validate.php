<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_animal_validate_insert;
DROP TRIGGER IF EXISTS trg_animal_validate_update;

CREATE TRIGGER trg_animal_validate_insert
BEFORE INSERT ON animal
FOR EACH ROW
BEGIN
    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal name is required';
    END IF;

    IF NEW.species IS NULL OR TRIM(NEW.species) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal species is required';
    END IF;

    IF NEW.gender IS NOT NULL AND NEW.gender NOT IN ('Male', 'Female', 'Unknown') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Gender must be Male, Female, or Unknown';
    END IF;

    IF NEW.weight IS NOT NULL AND NEW.weight < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Weight cannot be negative';
    END IF;

    IF NEW.adoption_status IS NOT NULL AND NEW.adoption_status NOT IN ('Available', 'Pending', 'Adopted', 'Not Adopted', 'Not Available') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid adoption status. Must be: Available, Pending, Adopted, Not Adopted, or Not Available';
    END IF;

    IF NEW.adoption_status IS NULL THEN
        SET NEW.adoption_status = 'Not Adopted';
    END IF;

    IF NEW.gender IS NULL THEN
        SET NEW.gender = 'Unknown';
    END IF;
END;

CREATE TRIGGER trg_animal_validate_update
BEFORE UPDATE ON animal
FOR EACH ROW
BEGIN
    IF NEW.name IS NULL OR TRIM(NEW.name) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal name is required';
    END IF;

    IF NEW.species IS NULL OR TRIM(NEW.species) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal species is required';
    END IF;

    IF NEW.gender IS NOT NULL AND NEW.gender NOT IN ('Male', 'Female', 'Unknown') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Gender must be Male, Female, or Unknown';
    END IF;

    IF NEW.weight IS NOT NULL AND NEW.weight < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Weight cannot be negative';
    END IF;

    IF NEW.adoption_status IS NOT NULL AND NEW.adoption_status NOT IN ('Available', 'Pending', 'Adopted', 'Not Adopted', 'Not Available') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid adoption status. Must be: Available, Pending, Adopted, Not Adopted, or Not Available';
    END IF;
END;
SQL;
