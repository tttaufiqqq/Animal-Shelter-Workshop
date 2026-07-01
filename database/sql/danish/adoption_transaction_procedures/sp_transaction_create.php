<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_transaction_create;

CREATE PROCEDURE sp_transaction_create(
    IN p_user_id BIGINT,
    IN p_amount DECIMAL(10,2),
    IN p_status VARCHAR(50),
    IN p_type VARCHAR(100),
    IN p_bill_code VARCHAR(255),
    IN p_reference_no VARCHAR(255),
    IN p_remarks TEXT,
    OUT o_transaction_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_transaction_id = NULL;
    END;

    IF p_user_id IS NULL THEN
        SET o_status = 'error'; SET o_message = 'User ID is required'; SET o_transaction_id = NULL;
        LEAVE proc_exit;
    END IF;

    IF p_amount IS NULL OR p_amount < 0 THEN
        SET o_status = 'error'; SET o_message = 'Valid amount is required'; SET o_transaction_id = NULL;
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    INSERT INTO `transaction` (userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at)
    VALUES (p_user_id, p_amount, p_status, p_type, p_bill_code, p_reference_no, p_remarks, NOW(), NOW());

    SET o_transaction_id = LAST_INSERT_ID();
    SET o_status = 'success';
    SET o_message = 'Transaction created successfully';

    COMMIT;
END;
SQL;
