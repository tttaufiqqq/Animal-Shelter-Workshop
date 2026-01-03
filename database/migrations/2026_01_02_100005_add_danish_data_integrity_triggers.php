<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = DB::connection('danish');

        // ===========================
        // TRIGGER: Cascade delete animal_booking when booking is deleted
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_booking_cascade_delete\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_booking_cascade_delete
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_cascade_delete
            ON booking
            AFTER DELETE
            AS
            BEGIN
                SET NOCOUNT ON;

                -- Delete related animal_booking records
                DELETE FROM animal_booking
                WHERE bookingID IN (SELECT id FROM deleted);
            END
        ");

        // ===========================
        // TRIGGER: Cascade delete visit_list_animal when visit_list is deleted
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_visit_list_cascade_delete\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_visit_list_cascade_delete
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_visit_list_cascade_delete
            ON visit_list
            AFTER DELETE
            AS
            BEGIN
                SET NOCOUNT ON;

                -- Delete related visit_list_animal records
                DELETE FROM visit_list_animal
                WHERE listID IN (SELECT id FROM deleted);
            END
        ");

        // ===========================
        // TRIGGER: Prevent booking deletion if adoptions exist
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_booking_prevent_delete_with_adoptions\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_booking_prevent_delete_with_adoptions
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_prevent_delete_with_adoptions
            ON booking
            INSTEAD OF DELETE
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_has_adoptions BIT;

                -- Check if any booking being deleted has adoptions
                SELECT @v_has_adoptions = 1
                FROM adoption
                WHERE bookingID IN (SELECT id FROM deleted);

                IF @v_has_adoptions = 1
                BEGIN
                    -- Raise error to prevent deletion
                    RAISERROR('Cannot delete booking with associated adoptions. Please delete adoptions first.', 16, 1);
                    ROLLBACK TRANSACTION;
                    RETURN;
                END

                -- Safe to delete - perform the actual deletion
                DELETE FROM animal_booking WHERE bookingID IN (SELECT id FROM deleted);
                DELETE FROM booking WHERE id IN (SELECT id FROM deleted);
            END
        ");

        // ===========================
        // TRIGGER: Prevent transaction deletion if adoptions exist
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_transaction_prevent_delete_with_adoptions\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_transaction_prevent_delete_with_adoptions
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_transaction_prevent_delete_with_adoptions
            ON [transaction]
            INSTEAD OF DELETE
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_has_adoptions BIT;

                -- Check if any transaction being deleted has adoptions
                SELECT @v_has_adoptions = 1
                FROM adoption
                WHERE transactionID IN (SELECT id FROM deleted);

                IF @v_has_adoptions = 1
                BEGIN
                    -- Raise error to prevent deletion
                    RAISERROR('Cannot delete transaction with associated adoptions. Please delete adoptions first.', 16, 1);
                    ROLLBACK TRANSACTION;
                    RETURN;
                END

                -- Safe to delete - perform the actual deletion
                DELETE FROM [transaction] WHERE id IN (SELECT id FROM deleted);
            END
        ");

        // ===========================
        // TRIGGER: Auto-update booking updated_at timestamp
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_booking_update_timestamp\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_booking_update_timestamp
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_update_timestamp
            ON booking
            AFTER UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;

                UPDATE booking
                SET updated_at = GETDATE()
                WHERE id IN (SELECT DISTINCT id FROM inserted);
            END
        ");

        // ===========================
        // TRIGGER: Auto-update visit_list updated_at timestamp
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_visit_list_update_timestamp\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_visit_list_update_timestamp
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_visit_list_update_timestamp
            ON visit_list
            AFTER UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;

                UPDATE visit_list
                SET updated_at = GETDATE()
                WHERE id IN (SELECT DISTINCT id FROM inserted);
            END
        ");

        // ===========================
        // TRIGGER: Auto-update adoption updated_at timestamp
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_adoption_update_timestamp\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_adoption_update_timestamp
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_adoption_update_timestamp
            ON adoption
            AFTER UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;

                UPDATE adoption
                SET updated_at = GETDATE()
                WHERE id IN (SELECT DISTINCT id FROM inserted);
            END
        ");

        // ===========================
        // TRIGGER: Auto-update transaction updated_at timestamp
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_transaction_update_timestamp\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_transaction_update_timestamp
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_transaction_update_timestamp
            ON [transaction]
            AFTER UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;

                UPDATE booking
                SET updated_at = GETDATE()
                WHERE id IN (SELECT DISTINCT id FROM inserted);
            END
        ");

        // ===========================
        // TRIGGER: Validate booking status transitions
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_booking_validate_status_transition\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_booking_validate_status_transition
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_validate_status_transition
            ON booking
            AFTER UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_old_status NVARCHAR(50);
                DECLARE @v_new_status NVARCHAR(50);
                DECLARE @v_booking_id BIGINT;

                -- Check each updated booking
                DECLARE status_cursor CURSOR FOR
                    SELECT i.id, d.status, i.status
                    FROM inserted i
                    INNER JOIN deleted d ON i.id = d.id
                    WHERE i.status != d.status;

                OPEN status_cursor;
                FETCH NEXT FROM status_cursor INTO @v_booking_id, @v_old_status, @v_new_status;

                WHILE @@FETCH_STATUS = 0
                BEGIN
                    -- Validate transitions
                    -- Completed bookings cannot be changed
                    IF @v_old_status = 'Completed' AND @v_new_status != 'Completed'
                    BEGIN
                        CLOSE status_cursor;
                        DEALLOCATE status_cursor;
                        RAISERROR('Cannot modify a completed booking', 16, 1);
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Adopted bookings cannot be changed
                    IF @v_old_status = 'Adopted' AND @v_new_status != 'Adopted'
                    BEGIN
                        CLOSE status_cursor;
                        DEALLOCATE status_cursor;
                        RAISERROR('Cannot modify an adopted booking', 16, 1);
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    FETCH NEXT FROM status_cursor INTO @v_booking_id, @v_old_status, @v_new_status;
                END

                CLOSE status_cursor;
                DEALLOCATE status_cursor;
            END
        ");

        // ===========================
        // TRIGGER: Validate appointment date is not in the past
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'trg_booking_validate_appointment_date\', \'TR\') IS NOT NULL
                DROP TRIGGER trg_booking_validate_appointment_date
        ');

        $connection->unprepared("
            CREATE TRIGGER trg_booking_validate_appointment_date
            ON booking
            AFTER INSERT, UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_past_date BIT;

                -- Check if any booking has appointment date in the past
                SELECT @v_past_date = 1
                FROM inserted
                WHERE appointment_date < CAST(GETDATE() AS DATE)
                  AND status = 'Pending'; -- Only validate for new/pending bookings

                IF @v_past_date = 1
                BEGIN
                    RAISERROR('Appointment date cannot be in the past', 16, 1);
                    ROLLBACK TRANSACTION;
                    RETURN;
                END
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('danish');

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_cascade_delete');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_cascade_delete');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_prevent_delete_with_adoptions');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_transaction_prevent_delete_with_adoptions');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_visit_list_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_adoption_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_transaction_update_timestamp');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_status_transition');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_booking_validate_appointment_date');
    }
};
