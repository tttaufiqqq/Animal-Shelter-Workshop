<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_rescue_read_by_caretaker;

CREATE PROCEDURE sp_rescue_read_by_caretaker(
    IN p_caretaker_id BIGINT,
    IN p_priority VARCHAR(20),
    IN p_status VARCHAR(50),
    IN p_offset INT,
    IN p_limit INT,
    OUT o_total_count INT
)
BEGIN
    SELECT COUNT(*) INTO o_total_count
    FROM rescue
    WHERE caretakerID = p_caretaker_id
      AND (p_priority IS NULL OR priority = p_priority)
      AND (p_status IS NULL OR status = p_status);

    SELECT id, status, priority, remarks, reportID, caretakerID, created_at, updated_at
    FROM rescue
    WHERE caretakerID = p_caretaker_id
      AND (p_priority IS NULL OR priority = p_priority)
      AND (p_status IS NULL OR status = p_status)
    ORDER BY
        CASE
            WHEN priority = 'critical' THEN 1
            WHEN priority = 'high' THEN 2
            WHEN priority = 'normal' THEN 3
            ELSE 4
        END,
        created_at DESC
    LIMIT p_limit OFFSET p_offset;
END;
SQL;
