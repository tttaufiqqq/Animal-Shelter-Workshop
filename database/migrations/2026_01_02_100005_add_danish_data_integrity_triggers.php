<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection('booking');

        // ===========================
        // TRIGGER: Cascade delete animal_booking when booking is deleted
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_cascade_delete');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_cascade_delete
            AFTER DELETE ON booking
            FOR EACH ROW
            BEGIN
                DELETE FROM animal_booking WHERE bookingID = OLD.id;
            END
        ");

        // ===========================
        // TRIGGER: Cascade delete visit_list_animal when visit_list is deleted
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_cascade_delete');

        $connection->unprepared("
            CREATE TRIGGER trg_visit_list_cascade_delete
            AFTER DELETE ON visit_list
            FOR EACH ROW
            BEGIN
                DELETE FROM visit_list_animal WHERE listID = OLD.id;
            END
        ");

        // ===========================
        // TRIGGER: Prevent booking deletion if adoptions exist
        // Note: MariaDB uses BEFORE DELETE + SIGNAL instead of INSTEAD OF DELETE
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_prevent_delete_with_adoptions');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_prevent_delete_with_adoptions
            BEFORE DELETE ON booking
            FOR EACH ROW
            BEGIN
                DECLARE v_count INT DEFAULT 0;
                SELECT COUNT(*) INTO v_count FROM adoption WHERE bookingID = OLD.id;
                IF v_count > 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot delete booking with associated adoptions. Please delete adoptions first.';
                END IF;
            END
        ");

        // ===========================
        // TRIGGER: Prevent transaction deletion if adoptions exist
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_transaction_prevent_delete_with_adoptions');

        $connection->unprepared("
            CREATE TRIGGER trg_transaction_prevent_delete_with_adoptions
            BEFORE DELETE ON `transaction`
            FOR EACH ROW
            BEGIN
                DECLARE v_count INT DEFAULT 0;
                SELECT COUNT(*) INTO v_count FROM adoption WHERE transactionID = OLD.id;
                IF v_count > 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot delete transaction with associated adoptions. Please delete adoptions first.';
                END IF;
            END
        ");

        // ===========================
        // TRIGGER: Auto-update booking updated_at timestamp
        // Uses BEFORE UPDATE so NEW.updated_at can be set directly
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_update_timestamp');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_update_timestamp
            BEFORE UPDATE ON booking
            FOR EACH ROW
            BEGIN
                SET NEW.updated_at = NOW();
            END
        ");

        // ===========================
        // TRIGGER: Auto-update visit_list updated_at timestamp
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_update_timestamp');

        $connection->unprepared("
            CREATE TRIGGER trg_visit_list_update_timestamp
            BEFORE UPDATE ON visit_list
            FOR EACH ROW
            BEGIN
                SET NEW.updated_at = NOW();
            END
        ");

        // ===========================
        // TRIGGER: Auto-update adoption updated_at timestamp
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_adoption_update_timestamp');

        $connection->unprepared("
            CREATE TRIGGER trg_adoption_update_timestamp
            BEFORE UPDATE ON adoption
            FOR EACH ROW
            BEGIN
                SET NEW.updated_at = NOW();
            END
        ");

        // ===========================
        // TRIGGER: Auto-update transaction updated_at timestamp
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_transaction_update_timestamp');

        $connection->unprepared("
            CREATE TRIGGER trg_transaction_update_timestamp
            BEFORE UPDATE ON `transaction`
            FOR EACH ROW
            BEGIN
                SET NEW.updated_at = NOW();
            END
        ");

        // ===========================
        // TRIGGER: Validate booking status transitions
        // Prevent modifying Completed or Adopted bookings
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_status_transition');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_validate_status_transition
            BEFORE UPDATE ON booking
            FOR EACH ROW
            BEGIN
                IF OLD.status = 'Completed' AND NEW.status != 'Completed' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot modify a completed booking';
                END IF;

                IF OLD.status = 'Adopted' AND NEW.status != 'Adopted' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot modify an adopted booking';
                END IF;
            END
        ");

        // ===========================
        // TRIGGER: Validate appointment date is not in the past (INSERT)
        // Note: MariaDB requires separate triggers for INSERT and UPDATE
        // ===========================
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_insert');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_validate_appointment_date_insert
            BEFORE INSERT ON booking
            FOR EACH ROW
            BEGIN
                IF NEW.appointment_date < CURDATE() AND NEW.status = 'Pending' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Appointment date cannot be in the past';
                END IF;
            END
        ");

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_update');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_validate_appointment_date_update
            BEFORE UPDATE ON booking
            FOR EACH ROW
            BEGIN
                IF NEW.appointment_date < CURDATE() AND NEW.status = 'Pending' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Appointment date cannot be in the past';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        $connection = DB::connection('booking');

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_cascade_delete');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_cascade_delete');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_prevent_delete_with_adoptions');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_transaction_prevent_delete_with_adoptions');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_adoption_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_transaction_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_status_transition');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date_update');
    }
};
