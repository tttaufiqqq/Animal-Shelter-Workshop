<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_slot_update;

CREATE PROCEDURE sp_slot_update(
    IN p_slot_id BIGINT,
    IN p_name VARCHAR(255),
    IN p_section_id BIGINT,
    IN p_capacity INT,
    IN p_status VARCHAR(50),
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_section_exists INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_exists FROM slot WHERE id = p_slot_id;

    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Slot not found'; ROLLBACK;
    ELSEIF p_name IS NULL OR TRIM(p_name) = '' THEN
        SET o_status = 'error'; SET o_message = 'Slot name is required'; ROLLBACK;
    ELSEIF p_section_id IS NULL THEN
        SET o_status = 'error'; SET o_message = 'Section ID is required'; ROLLBACK;
    ELSEIF p_capacity IS NULL OR p_capacity < 1 THEN
        SET o_status = 'error'; SET o_message = 'Capacity must be at least 1'; ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_section_exists FROM section WHERE id = p_section_id;

        IF v_section_exists = 0 THEN
            SET o_status = 'error'; SET o_message = 'The selected section does not exist'; ROLLBACK;
        ELSE
            UPDATE slot
            SET name = p_name, sectionID = p_section_id, capacity = p_capacity,
                status = COALESCE(p_status, status), updated_at = NOW()
            WHERE id = p_slot_id;

            SET o_status = 'success';
            SET o_message = 'Slot updated successfully';
            COMMIT;
        END IF;
    END IF;
END;
SQL;
