<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_transaction_get_by_bill_code;

CREATE PROCEDURE sp_transaction_get_by_bill_code(
    IN p_bill_code VARCHAR(255)
)
BEGIN
    SELECT id, userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at
    FROM `transaction`
    WHERE bill_code = p_bill_code;
END;
SQL;
