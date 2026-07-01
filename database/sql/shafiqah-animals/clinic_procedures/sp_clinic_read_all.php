<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_clinic_read_all;

CREATE PROCEDURE sp_clinic_read_all()
BEGIN
    SELECT
        c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude,
        c.created_at, c.updated_at,
        COUNT(v.id) as vet_count
    FROM clinic c
    LEFT JOIN vet v ON c.id = v.clinicID
    GROUP BY c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude, c.created_at, c.updated_at
    ORDER BY c.name;
END;
SQL;
