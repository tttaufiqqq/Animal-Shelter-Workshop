<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_visit_list_remove_animal;

CREATE PROCEDURE sp_visit_list_remove_animal(
    IN p_list_id BIGINT,
    IN p_animal_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_animal_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
    END;

    SELECT COUNT(*) INTO v_exists FROM visit_list WHERE id = p_list_id;
    IF v_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Visit list not found';
        LEAVE proc_exit;
    END IF;

    SELECT COUNT(*) INTO v_animal_exists FROM visit_list_animal WHERE listID = p_list_id AND animalID = p_animal_id;
    IF v_animal_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Animal not found in visit list';
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    DELETE FROM visit_list_animal WHERE listID = p_list_id AND animalID = p_animal_id;
    UPDATE visit_list SET updated_at = NOW() WHERE id = p_list_id;

    SET o_status = 'success';
    SET o_message = 'Animal removed from visit list successfully';

    COMMIT;
END;
SQL;
