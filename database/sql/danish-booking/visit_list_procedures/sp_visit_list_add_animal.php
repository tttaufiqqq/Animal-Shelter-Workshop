<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_visit_list_add_animal;

CREATE PROCEDURE sp_visit_list_add_animal(
    IN p_list_id BIGINT,
    IN p_animal_id BIGINT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_duplicate INT DEFAULT 0;

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

    SELECT COUNT(*) INTO v_duplicate FROM visit_list_animal WHERE listID = p_list_id AND animalID = p_animal_id;
    IF v_duplicate > 0 THEN
        SET o_status = 'error'; SET o_message = 'This animal is already in your visit list';
        LEAVE proc_exit;
    END IF;

    START TRANSACTION;

    INSERT INTO visit_list_animal (listID, animalID, created_at, updated_at) VALUES (p_list_id, p_animal_id, NOW(), NOW());
    UPDATE visit_list SET updated_at = NOW() WHERE id = p_list_id;

    SET o_status = 'success';
    SET o_message = 'Animal added to visit list successfully';

    COMMIT;
END;
SQL;
