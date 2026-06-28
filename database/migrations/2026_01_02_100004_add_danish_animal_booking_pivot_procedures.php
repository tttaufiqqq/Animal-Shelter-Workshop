<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection('booking');

        // ===========================
        // sp_booking_attach_animals
        // Parses comma-separated 'animalId:remarks' pairs and inserts into pivot table
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_attach_animals');

        $connection->unprepared("
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
                    SET o_status = 'error';
                    SET o_message = 'Booking not found';
                    SET o_attached_count = 0;
                    LEAVE proc_exit;
                END IF;

                SET o_attached_count = 0;
                -- Append trailing comma so every pair ends with ','
                SET v_pairs_list = CONCAT(p_animal_ids, ',');
                SET v_total = LENGTH(p_animal_ids) - LENGTH(REPLACE(p_animal_ids, ',', '')) + 1;

                START TRANSACTION;

                WHILE v_i <= v_total DO
                    -- Extract the i-th comma-delimited token
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

                        -- Skip if already exists in this booking
                        IF NOT EXISTS (
                            SELECT 1 FROM animal_booking
                            WHERE bookingID = p_booking_id AND animalID = v_animal_id
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
            END
        ");

        // ===========================
        // sp_booking_detach_animals
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_detach_animals');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_detach_animals(
                IN p_booking_id BIGINT,
                IN p_animal_ids TEXT,
                OUT o_detached_count INT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_booking_exists INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_detached_count = 0;
                END;

                SELECT COUNT(*) INTO v_booking_exists FROM booking WHERE id = p_booking_id;
                IF v_booking_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Booking not found';
                    SET o_detached_count = 0;
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                IF p_animal_ids IS NULL THEN
                    DELETE FROM animal_booking WHERE bookingID = p_booking_id;
                ELSE
                    DELETE FROM animal_booking
                    WHERE bookingID = p_booking_id
                      AND FIND_IN_SET(animalID, p_animal_ids) > 0;
                END IF;

                SET o_detached_count = ROW_COUNT();
                SET o_status = 'success';
                SET o_message = CONCAT(o_detached_count, ' animal(s) detached from booking');

                COMMIT;
            END
        ");

        // ===========================
        // sp_booking_get_animals
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animals');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_get_animals(
                IN p_booking_id BIGINT
            )
            BEGIN
                SELECT animalID, remarks, created_at, updated_at
                FROM animal_booking
                WHERE bookingID = p_booking_id
                ORDER BY created_at;
            END
        ");

        // ===========================
        // sp_booking_update_animal_remarks
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_animal_remarks');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_update_animal_remarks(
                IN p_booking_id BIGINT,
                IN p_animal_id BIGINT,
                IN p_remarks VARCHAR(500),
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
                END;

                SELECT COUNT(*) INTO v_exists
                FROM animal_booking
                WHERE bookingID = p_booking_id AND animalID = p_animal_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found in this booking';
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                UPDATE animal_booking
                SET remarks = p_remarks,
                    updated_at = NOW()
                WHERE bookingID = p_booking_id AND animalID = p_animal_id;

                SET o_status = 'success';
                SET o_message = 'Remarks updated successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_booking_get_animal_count
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animal_count');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_get_animal_count(
                IN p_booking_id BIGINT,
                OUT o_count INT
            )
            BEGIN
                SELECT COUNT(*) INTO o_count
                FROM animal_booking
                WHERE bookingID = p_booking_id;
            END
        ");
    }

    public function down(): void
    {
        $connection = DB::connection('booking');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_attach_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_detach_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_animal_remarks');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animal_count');
    }
};
