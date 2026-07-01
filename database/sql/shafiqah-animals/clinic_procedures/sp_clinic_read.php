<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_clinic_read;

CREATE PROCEDURE sp_clinic_read(
    IN p_clinic_id BIGINT
)
BEGIN
    SELECT id, name, address, contactNum, latitude, longitude, created_at, updated_at
    FROM clinic
    WHERE id = p_clinic_id;
END;
SQL;
