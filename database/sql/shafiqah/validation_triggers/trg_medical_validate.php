<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_medical_validate_insert;
DROP TRIGGER IF EXISTS trg_medical_validate_update;

CREATE TRIGGER trg_medical_validate_insert
BEFORE INSERT ON medical
FOR EACH ROW
BEGIN
    IF NEW.animalID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for medical record';
    END IF;

    IF NEW.treatment_type IS NULL OR TRIM(NEW.treatment_type) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Treatment type is required';
    END IF;

    IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Medical costs cannot be negative';
    END IF;
END;

CREATE TRIGGER trg_medical_validate_update
BEFORE UPDATE ON medical
FOR EACH ROW
BEGIN
    IF NEW.animalID IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Animal ID is required for medical record';
    END IF;

    IF NEW.treatment_type IS NULL OR TRIM(NEW.treatment_type) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Treatment type is required';
    END IF;

    IF NEW.costs IS NOT NULL AND NEW.costs < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Medical costs cannot be negative';
    END IF;
END;
SQL;
