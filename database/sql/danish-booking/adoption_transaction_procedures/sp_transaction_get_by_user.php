<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_transaction_get_by_user;

CREATE PROCEDURE sp_transaction_get_by_user(
    IN p_user_id BIGINT,
    IN p_status VARCHAR(50)
)
BEGIN
    SELECT id, userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at
    FROM `transaction`
    WHERE userID = p_user_id
      AND (p_status IS NULL OR status = p_status)
    ORDER BY created_at DESC;
END;
SQL;
