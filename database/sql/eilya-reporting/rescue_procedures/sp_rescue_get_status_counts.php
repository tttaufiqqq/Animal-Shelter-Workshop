<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_rescue_get_status_counts;

CREATE PROCEDURE sp_rescue_get_status_counts(
    IN p_caretaker_id BIGINT
)
BEGIN
    SELECT status, COUNT(*) as total
    FROM rescue
    WHERE caretakerID = p_caretaker_id
    GROUP BY status;
END;
SQL;
