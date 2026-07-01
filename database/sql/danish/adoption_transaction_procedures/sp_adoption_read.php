<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_adoption_read;

CREATE PROCEDURE sp_adoption_read(
    IN p_adoption_id BIGINT
)
BEGIN
    SELECT id, bookingID, transactionID, animalID, fee, remarks, created_at, updated_at
    FROM adoption
    WHERE id = p_adoption_id;
END;
SQL;
