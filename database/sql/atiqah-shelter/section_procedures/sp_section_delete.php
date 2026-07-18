<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_section_delete;

CREATE PROCEDURE sp_section_delete(
    IN p_section_id BIGINT,
    IN p_user_id BIGINT,
    IN p_user_name VARCHAR(255),
    IN p_user_email VARCHAR(255),
    OUT o_has_slots BOOLEAN,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_slot_count INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
        ROLLBACK;
        SET o_status = 'error';
        SET o_has_slots = FALSE;
    END;

    START TRANSACTION;

    SET o_has_slots = FALSE;

    SELECT COUNT(*) INTO v_exists FROM section WHERE id = p_section_id;

    IF v_exists = 0 THEN
        SET o_status = 'error';
        SET o_message = 'Section not found';
        ROLLBACK;
    ELSE
        SELECT COUNT(*) INTO v_slot_count FROM slot WHERE sectionID = p_section_id;

        IF v_slot_count > 0 THEN
            SET o_status = 'error';
            SET o_message = 'Cannot delete section with existing slots. Please delete or move the slots first.';
            SET o_has_slots = TRUE;
            ROLLBACK;
        ELSE
            DELETE FROM section WHERE id = p_section_id;

            SET o_status = 'success';
            SET o_message = 'Section deleted successfully';
            COMMIT;
        END IF;
    END IF;
END;
SQL;
