<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_slot_create;

CREATE PROCEDURE sp_slot_create(
    IN p_name VARCHAR(255),
    IN p_section_id BIGINT,
    IN p_capacity INT,
    IN p_status VARCHAR(50),
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_slot_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_section_exists INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        SET o_status = 'error';
        SET o_slot_id = NULL;
    END;

    START TRANSACTION;

    IF p_name IS NULL OR TRIM(p_name) = '' THEN
        SET o_status = 'error'; SET o_message = 'Slot name is required'; SET o_slot_id = NULL; ROLLBACK;
    ELSEIF p_section_id IS NULL THEN
        SET o_status = 'error'; SET o_message = 'Section ID is required'; SET o_slot_id = NULL; ROLLBACK;
    ELSEIF p_capacity IS NULL OR p_capacity < 1 THEN
        SET o_status = 'error'; SET o_message = 'Capacity must be at least 1'; SET o_slot_id = NULL; ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_section_exists FROM section WHERE id = p_section_id;

        IF v_section_exists = 0 THEN
            SET o_status = 'error'; SET o_message = 'The selected section does not exist'; SET o_slot_id = NULL; ROLLBACK;
        ELSE
            INSERT INTO slot (name, sectionID, capacity, status, created_at, updated_at)
            VALUES (p_name, p_section_id, p_capacity, COALESCE(p_status, 'available'), NOW(), NOW());

            SET o_slot_id = LAST_INSERT_ID();
            SET o_status = 'success';
            SET o_message = 'Slot created successfully';
            COMMIT;
        END IF;
    END IF;
END;
SQL;
