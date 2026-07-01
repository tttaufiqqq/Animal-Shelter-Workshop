<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_visit_list_clear_animals;

CREATE PROCEDURE sp_visit_list_clear_animals(
    IN p_list_id BIGINT,
    IN p_animal_ids TEXT,
    OUT o_removed_count INT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_removed_count = 0;
    END;

    SELECT COUNT(*) INTO v_exists FROM visit_list WHERE id = p_list_id;
    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Visit list not found'; SET o_removed_count = 0;
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    DELETE FROM visit_list_animal
    WHERE listID = p_list_id
      AND FIND_IN_SET(animalID, p_animal_ids) > 0;

    SET o_removed_count = ROW_COUNT();

    UPDATE visit_list SET updated_at = NOW() WHERE id = p_list_id;

    SET o_status = 'success';
    SET o_message = CONCAT(o_removed_count, ' animal(s) removed from visit list');

    COMMIT;
END;
SQL;
