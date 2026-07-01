<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_report_create;

CREATE PROCEDURE sp_report_create(
    IN p_latitude DECIMAL(10,8),
    IN p_longitude DECIMAL(11,8),
    IN p_address VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(100),
    IN p_report_status VARCHAR(50),
    IN p_description TEXT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_report_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_report_id = NULL;
    END;

    START TRANSACTION;

    IF p_latitude IS NULL OR p_longitude IS NULL THEN
        SET o_status = 'error';
        SET o_message = 'Latitude and longitude are required';
        SET o_report_id = NULL;
        ROLLBACK;
    ELSEIF p_address IS NULL OR TRIM(p_address) = '' THEN
        SET o_status = 'error';
        SET o_message = 'Address is required';
        SET o_report_id = NULL;
        ROLLBACK;
    ELSEIF p_city IS NULL OR TRIM(p_city) = '' THEN
        SET o_status = 'error';
        SET o_message = 'City is required';
        SET o_report_id = NULL;
        ROLLBACK;
    ELSEIF p_description IS NULL OR TRIM(p_description) = '' THEN
        SET o_status = 'error';
        SET o_message = 'Description is required';
        SET o_report_id = NULL;
        ROLLBACK;
    ELSE
        INSERT INTO report (
            latitude, longitude, address, city, state, report_status,
            description, userID, created_at, updated_at
        )
        VALUES (
            p_latitude, p_longitude, p_address, p_city, p_state,
            COALESCE(p_report_status, 'Pending'),
            p_description, p_user_id, NOW(), NOW()
        );

        SET o_report_id = LAST_INSERT_ID();
        SET o_status = 'success';
        SET o_message = 'Report created successfully';
        COMMIT;
    END IF;
END;
SQL;
