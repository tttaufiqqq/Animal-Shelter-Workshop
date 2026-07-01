<?php

return <<<'SQL'
DROP TRIGGER IF EXISTS trg_transaction_prevent_delete_with_adoptions;
DROP TRIGGER IF EXISTS trg_transaction_update_timestamp;

CREATE TRIGGER trg_transaction_prevent_delete_with_adoptions
BEFORE DELETE ON `transaction`
FOR EACH ROW
BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count FROM adoption WHERE transactionID = OLD.id;
    IF v_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete transaction with associated adoptions. Please delete adoptions first.';
    END IF;
END;

CREATE TRIGGER trg_transaction_update_timestamp
BEFORE UPDATE ON `transaction`
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END;
SQL;
