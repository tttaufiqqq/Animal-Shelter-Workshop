<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_adoption_create;

CREATE PROCEDURE sp_adoption_create(
    IN p_booking_id BIGINT,
    IN p_transaction_id BIGINT,
    IN p_animal_id BIGINT,
    IN p_fee DECIMAL(10,2),
    IN p_remarks TEXT,
    OUT o_adoption_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_booking_exists INT DEFAULT 0;
    DECLARE v_transaction_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_adoption_id = NULL;
    END;

    SELECT COUNT(*) INTO v_booking_exists FROM booking WHERE id = p_booking_id;
    IF v_booking_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Booking not found'; SET o_adoption_id = NULL;
        LEAVE proc_exit;
    END IF;

    SELECT COUNT(*) INTO v_transaction_exists FROM `transaction` WHERE id = p_transaction_id;
    IF v_transaction_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Transaction not found'; SET o_adoption_id = NULL;
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    INSERT INTO adoption (bookingID, transactionID, animalID, fee, remarks, created_at, updated_at)
    VALUES (p_booking_id, p_transaction_id, p_animal_id, p_fee, p_remarks, NOW(), NOW());

    SET o_adoption_id = LAST_INSERT_ID();
    SET o_status = 'success';
    SET o_message = 'Adoption record created successfully';

    COMMIT;
END;
SQL;
