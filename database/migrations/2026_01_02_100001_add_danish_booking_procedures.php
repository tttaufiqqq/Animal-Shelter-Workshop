<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection('booking');

        // ===========================
        // sp_booking_create
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_create');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_create(
                IN p_user_id BIGINT,
                IN p_appointment_date DATE,
                IN p_appointment_time TIME,
                IN p_status VARCHAR(50),
                OUT o_booking_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_booking_id = NULL;
                END;

                SET p_status = IFNULL(p_status, 'Pending');

                IF p_user_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'User ID is required';
                    SET o_booking_id = NULL;
                    LEAVE proc_exit;
                END IF;

                IF p_appointment_date IS NULL OR p_appointment_time IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Appointment date and time are required';
                    SET o_booking_id = NULL;
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                INSERT INTO booking (userID, appointment_date, appointment_time, status, created_at, updated_at)
                VALUES (p_user_id, p_appointment_date, p_appointment_time, p_status, NOW(), NOW());

                SET o_booking_id = LAST_INSERT_ID();
                SET o_status = 'success';
                SET o_message = 'Booking created successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_booking_read
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_read');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_read(
                IN p_booking_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    userID,
                    appointment_date,
                    appointment_time,
                    status,
                    remarks,
                    created_at,
                    updated_at
                FROM booking
                WHERE id = p_booking_id;
            END
        ");

        // ===========================
        // sp_booking_update_status
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_status');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_update_status(
                IN p_booking_id BIGINT,
                IN p_new_status VARCHAR(50),
                IN p_user_id BIGINT,
                OUT o_old_status VARCHAR(50),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_exists INT DEFAULT 0;
                DECLARE v_booking_user_id BIGINT DEFAULT NULL;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_old_status = NULL;
                END;

                SELECT COUNT(*), status, userID
                INTO v_exists, o_old_status, v_booking_user_id
                FROM booking
                WHERE id = p_booking_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Booking not found';
                    SET o_old_status = NULL;
                    LEAVE proc_exit;
                END IF;

                IF p_user_id IS NOT NULL AND v_booking_user_id != p_user_id THEN
                    SET o_status = 'error';
                    SET o_message = 'Unauthorized: This booking belongs to another user';
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                UPDATE booking
                SET status = p_new_status,
                    updated_at = NOW()
                WHERE id = p_booking_id;

                SET o_status = 'success';
                SET o_message = 'Booking status updated successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_booking_cancel
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_cancel');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_cancel(
                IN p_booking_id BIGINT,
                IN p_user_id BIGINT,
                OUT o_old_status VARCHAR(50),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_exists INT DEFAULT 0;
                DECLARE v_booking_user_id BIGINT DEFAULT NULL;
                DECLARE v_current_status VARCHAR(50) DEFAULT NULL;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_old_status = NULL;
                END;

                SELECT COUNT(*), status, userID
                INTO v_exists, v_current_status, v_booking_user_id
                FROM booking
                WHERE id = p_booking_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Booking not found';
                    SET o_old_status = NULL;
                    LEAVE proc_exit;
                END IF;

                IF v_booking_user_id != p_user_id THEN
                    SET o_status = 'error';
                    SET o_message = 'Unauthorized: This booking belongs to another user';
                    SET o_old_status = v_current_status;
                    LEAVE proc_exit;
                END IF;

                IF v_current_status NOT IN ('Pending', 'Confirmed') THEN
                    SET o_status = 'error';
                    SET o_message = CONCAT('Cannot cancel booking with status: ', v_current_status);
                    SET o_old_status = v_current_status;
                    LEAVE proc_exit;
                END IF;

                SET o_old_status = v_current_status;

                START TRANSACTION;

                UPDATE booking
                SET status = 'Cancelled',
                    updated_at = NOW()
                WHERE id = p_booking_id;

                SET o_status = 'success';
                SET o_message = 'Booking cancelled successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_booking_check_time_conflicts
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_check_time_conflicts');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_check_time_conflicts(
                IN p_appointment_date DATE,
                IN p_appointment_time TIME,
                IN p_animal_ids TEXT,
                IN p_exclude_booking_id BIGINT
            )
            BEGIN
                SELECT DISTINCT ab.animalID
                FROM booking b
                INNER JOIN animal_booking ab ON b.id = ab.bookingID
                WHERE b.appointment_date = p_appointment_date
                  AND b.appointment_time = p_appointment_time
                  AND b.status IN ('Pending', 'Confirmed')
                  AND (p_exclude_booking_id IS NULL OR b.id != p_exclude_booking_id)
                  AND FIND_IN_SET(ab.animalID, p_animal_ids) > 0;
            END
        ");
    }

    public function down(): void
    {
        $connection = DB::connection('booking');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_status');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_cancel');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_check_time_conflicts');
    }
};
