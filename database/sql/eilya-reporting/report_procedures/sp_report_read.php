<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_report_read;

CREATE PROCEDURE sp_report_read(
    IN p_report_id BIGINT
)
BEGIN
    SELECT id, latitude, longitude, address, city, state, report_status,
           description, userID, created_at, updated_at
    FROM report
    WHERE id = p_report_id;
END;
SQL;
