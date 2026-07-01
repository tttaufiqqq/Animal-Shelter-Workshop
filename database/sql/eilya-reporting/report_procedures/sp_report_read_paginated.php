<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_report_read_paginated;

CREATE PROCEDURE sp_report_read_paginated(
    IN p_user_id BIGINT,
    IN p_status VARCHAR(50),
    IN p_city VARCHAR(100),
    IN p_offset INT,
    IN p_limit INT,
    OUT o_total_count INT
)
BEGIN
    SELECT COUNT(*) INTO o_total_count
    FROM report
    WHERE (p_user_id IS NULL OR userID = p_user_id)
      AND (p_status IS NULL OR report_status = p_status)
      AND (p_city IS NULL OR city = p_city);

    SELECT id, latitude, longitude, address, city, state, report_status,
           description, userID, created_at, updated_at
    FROM report
    WHERE (p_user_id IS NULL OR userID = p_user_id)
      AND (p_status IS NULL OR report_status = p_status)
      AND (p_city IS NULL OR city = p_city)
    ORDER BY created_at DESC
    LIMIT p_limit OFFSET p_offset;
END;
SQL;
