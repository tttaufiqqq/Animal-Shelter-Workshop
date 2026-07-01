<?php

return <<<'SQL'
DROP PROCEDURE IF EXISTS sp_booking_attach_animals;

CREATE PROCEDURE sp_booking_attach_animals(
    IN p_booking_id BIGINT,
    IN p_animal_ids TEXT,
    OUT o_attached_count INT,
    OUT o_status VARCHAR(20),
    OUT o_message TEXT
)
proc_exit: BEGIN
    DECLARE v_booking_exists INT DEFAULT 0;
    DECLARE v_total INT DEFAULT 0;
    DECLARE v_i INT DEFAULT 1;
    DECLARE v_pair TEXT;
    DECLARE v_pos INT;
    DECLARE v_animal_id BIGINT;
    DECLARE v_remarks VARCHAR(500);
    DECLARE v_pairs_list TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_status = 'error';
        SET o_message = 'Database error occurred';
        SET o_attached_count = 0;
    END;

    SELECT COUNT(*) INTO v_booking_exists FROM booking WHERE id = p_booking_id;
    IF v_booking_exists = 0 THEN
        SET o_status = 'error'; SET o_message = 'Booking not found'; SET o_attached_count = 0;
        LEAVE proc_exit;
    END IF;

    SET o_attached_count = 0;
    SET v_pairs_list = CONCAT(p_animal_ids, ',');
    SET v_total = LENGTH(p_animal_ids) - LENGTH(REPLACE(p_animal_ids, ',', '')) + 1;

    START TRANSACTION;

    WHILE v_i <= v_total DO
        SET v_pair = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(v_pairs_list, ',', v_i), ',', -1));

        IF v_pair != '' THEN
            SET v_pos = LOCATE(':', v_pair);

            IF v_pos > 0 THEN
                SET v_animal_id = CAST(LEFT(v_pair, v_pos - 1) AS UNSIGNED);
                SET v_remarks = NULLIF(TRIM(SUBSTRING(v_pair, v_pos + 1)), '');
            ELSE
                SET v_animal_id = CAST(v_pair AS UNSIGNED);
                SET v_remarks = NULL;
            END IF;

            IF NOT EXISTS (
                SELECT 1 FROM animal_booking WHERE bookingID = p_booking_id AND animalID = v_animal_id
            ) THEN
                INSERT INTO animal_booking (bookingID, animalID, remarks, created_at, updated_at)
                VALUES (p_booking_id, v_animal_id, v_remarks, NOW(), NOW());
                SET o_attached_count = o_attached_count + 1;
            END IF;
        END IF;

        SET v_i = v_i + 1;
    END WHILE;

    SET o_status = 'success';
    SET o_message = CONCAT(o_attached_count, ' animal(s) attached to booking');

    COMMIT;
END;
SQL;
