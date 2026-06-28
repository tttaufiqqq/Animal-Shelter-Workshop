<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection('booking');

        // ===========================
        // ADOPTION PROCEDURES
        // ===========================

        // sp_adoption_create
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_create');

        $connection->unprepared("
            CREATE PROCEDURE sp_adoption_create(
                IN p_booking_id BIGINT,
                IN p_transaction_id BIGINT,
                IN p_animal_id BIGINT,
                IN p_fee DECIMAL(10,2),
                IN p_remarks TEXT,
                OUT o_adoption_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_booking_exists INT DEFAULT 0;
                DECLARE v_transaction_exists INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_adoption_id = NULL;
                END;

                SELECT COUNT(*) INTO v_booking_exists FROM booking WHERE id = p_booking_id;
                IF v_booking_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Booking not found';
                    SET o_adoption_id = NULL;
                    LEAVE proc_exit;
                END IF;

                SELECT COUNT(*) INTO v_transaction_exists FROM `transaction` WHERE id = p_transaction_id;
                IF v_transaction_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Transaction not found';
                    SET o_adoption_id = NULL;
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                INSERT INTO adoption (bookingID, transactionID, animalID, fee, remarks, created_at, updated_at)
                VALUES (p_booking_id, p_transaction_id, p_animal_id, p_fee, p_remarks, NOW(), NOW());

                SET o_adoption_id = LAST_INSERT_ID();
                SET o_status = 'success';
                SET o_message = 'Adoption record created successfully';

                COMMIT;
            END
        ");

        // sp_adoption_read
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_read');

        $connection->unprepared("
            CREATE PROCEDURE sp_adoption_read(
                IN p_adoption_id BIGINT
            )
            BEGIN
                SELECT id, bookingID, transactionID, animalID, fee, remarks, created_at, updated_at
                FROM adoption
                WHERE id = p_adoption_id;
            END
        ");

        // sp_adoption_get_by_booking
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_get_by_booking');

        $connection->unprepared("
            CREATE PROCEDURE sp_adoption_get_by_booking(
                IN p_booking_id BIGINT
            )
            BEGIN
                SELECT id, bookingID, transactionID, animalID, fee, remarks, created_at, updated_at
                FROM adoption
                WHERE bookingID = p_booking_id
                ORDER BY created_at DESC;
            END
        ");

        // ===========================
        // TRANSACTION PROCEDURES
        // ===========================

        // sp_transaction_create
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_create');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_create(
                IN p_user_id BIGINT,
                IN p_amount DECIMAL(10,2),
                IN p_status VARCHAR(50),
                IN p_type VARCHAR(100),
                IN p_bill_code VARCHAR(255),
                IN p_reference_no VARCHAR(255),
                IN p_remarks TEXT,
                OUT o_transaction_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_transaction_id = NULL;
                END;

                IF p_user_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'User ID is required';
                    SET o_transaction_id = NULL;
                    LEAVE proc_exit;
                END IF;

                IF p_amount IS NULL OR p_amount < 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Valid amount is required';
                    SET o_transaction_id = NULL;
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                INSERT INTO `transaction` (userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at)
                VALUES (p_user_id, p_amount, p_status, p_type, p_bill_code, p_reference_no, p_remarks, NOW(), NOW());

                SET o_transaction_id = LAST_INSERT_ID();
                SET o_status = 'success';
                SET o_message = 'Transaction created successfully';

                COMMIT;
            END
        ");

        // sp_transaction_read
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_read');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_read(
                IN p_transaction_id BIGINT
            )
            BEGIN
                SELECT id, userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at
                FROM `transaction`
                WHERE id = p_transaction_id;
            END
        ");

        // sp_transaction_update_status
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_update_status');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_update_status(
                IN p_transaction_id BIGINT,
                IN p_new_status VARCHAR(50),
                OUT o_old_status VARCHAR(50),
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
                    SET o_old_status = NULL;
                END;

                SELECT COUNT(*), status
                INTO v_exists, o_old_status
                FROM `transaction`
                WHERE id = p_transaction_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Transaction not found';
                    SET o_old_status = NULL;
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                UPDATE `transaction`
                SET status = p_new_status,
                    updated_at = NOW()
                WHERE id = p_transaction_id;

                SET o_status = 'success';
                SET o_message = 'Transaction status updated successfully';

                COMMIT;
            END
        ");

        // sp_transaction_get_by_bill_code
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_bill_code');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_get_by_bill_code(
                IN p_bill_code VARCHAR(255)
            )
            BEGIN
                SELECT id, userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at
                FROM `transaction`
                WHERE bill_code = p_bill_code;
            END
        ");

        // sp_transaction_get_by_user
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_user');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_get_by_user(
                IN p_user_id BIGINT,
                IN p_status VARCHAR(50)
            )
            BEGIN
                SELECT id, userID, amount, status, type, bill_code, reference_no, remarks, created_at, updated_at
                FROM `transaction`
                WHERE userID = p_user_id
                  AND (p_status IS NULL OR status = p_status)
                ORDER BY created_at DESC;
            END
        ");
    }

    public function down(): void
    {
        $connection = DB::connection('booking');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_get_by_booking');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_update_status');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_bill_code');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_user');
    }
};
