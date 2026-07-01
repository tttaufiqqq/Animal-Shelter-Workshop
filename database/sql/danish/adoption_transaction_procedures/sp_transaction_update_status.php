<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_transaction_update_status;

CREATE PROCEDURE sp_transaction_update_status(
    IN p_transaction_id BIGINT,
    IN p_new_status VARCHAR(50),
    OUT o_old_status VARCHAR(50),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_old_status = NULL;
    END;

    SELECT COUNT(*), status INTO v_exists, o_old_status FROM `transaction` WHERE id = p_transaction_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Transaction not found'; SET o_old_status = NULL;
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    UPDATE `transaction` SET status = p_new_status, updated_at = NOW() WHERE id = p_transaction_id;

    SET o_status = 'success';
    SET o_message = 'Transaction status updated successfully';

    COMMIT;
END;
SQL;
