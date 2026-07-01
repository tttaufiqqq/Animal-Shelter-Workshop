<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_vet_read_all;

CREATE PROCEDURE sp_vet_read_all()
BEGIN
    SELECT
        v.id, v.name, v.email, v.contactNum, v.specialization, v.license_no,
        v.clinicID, c.name as clinic_name, v.created_at, v.updated_at
    FROM vet v
    LEFT JOIN clinic c ON v.clinicID = c.id
    ORDER BY v.name;
END;
SQL;
