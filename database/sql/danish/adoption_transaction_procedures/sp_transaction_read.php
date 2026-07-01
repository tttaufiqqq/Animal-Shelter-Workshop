<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_transaction_read;

CREATE PROCEDURE sp_transaction_read(
    IN p_transaction_id BIGINT
)
BEGIN
    SELECT id, userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at
    FROM `transaction`
    WHERE id = p_transaction_id;
END;
SQL;
