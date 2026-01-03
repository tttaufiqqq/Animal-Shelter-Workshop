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
        // sp_booking_create
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_create\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_create
                @p_user_id BIGINT,
                @p_appointment_date DATE,
                @p_appointment_time TIME,
                @p_status NVARCHAR(50) = 'Pending',
                @o_booking_id BIGINT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Validation: Check required fields
                    IF @p_user_id IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'User ID is required';
                        SET @o_booking_id = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    IF @p_appointment_date IS NULL OR @p_appointment_time IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Appointment date and time are required';
                        SET @o_booking_id = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Insert booking
                    INSERT INTO booking (userID, appointment_date, appointment_time, status, created_at, updated_at)
                    VALUES (@p_user_id, @p_appointment_date, @p_appointment_time, @p_status, GETDATE(), GETDATE());

                    SET @o_booking_id = SCOPE_IDENTITY();
                    SET @o_status = 'success';
                    SET @o_message = 'Booking created successfully';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_booking_id = NULL;
                END CATCH
            END
        ");

        // ===========================
        // sp_booking_read
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_read\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_read
                @p_booking_id BIGINT
            AS
            BEGIN
                SET NOCOUNT ON;

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
                WHERE id = @p_booking_id;
            END
        ");

        // ===========================
        // sp_booking_update_status
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_update_status\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_update_status
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_update_status
                @p_booking_id BIGINT,
                @p_new_status NVARCHAR(50),
                @p_user_id BIGINT = NULL,
                @o_old_status NVARCHAR(50) OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;
                DECLARE @v_booking_user_id BIGINT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if booking exists and get current status
                    SELECT @v_exists = 1, @o_old_status = status, @v_booking_user_id = userID
                    FROM booking
                    WHERE id = @p_booking_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Booking not found';
                        SET @o_old_status = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Optionally check authorization
                    IF @p_user_id IS NOT NULL AND @v_booking_user_id != @p_user_id
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Unauthorized: This booking belongs to another user';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Update booking status
                    UPDATE booking
                    SET status = @p_new_status,
                        updated_at = GETDATE()
                    WHERE id = @p_booking_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Booking status updated successfully';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_old_status = NULL;
                END CATCH
            END
        ");

        // ===========================
        // sp_booking_cancel
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_cancel\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_cancel
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_cancel
                @p_booking_id BIGINT,
                @p_user_id BIGINT,
                @o_old_status NVARCHAR(50) OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;
                DECLARE @v_booking_user_id BIGINT;
                DECLARE @v_current_status NVARCHAR(50);

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if booking exists and belongs to user
                    SELECT @v_exists = 1, @v_current_status = status, @v_booking_user_id = userID
                    FROM booking
                    WHERE id = @p_booking_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Booking not found';
                        SET @o_old_status = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Check authorization
                    IF @v_booking_user_id != @p_user_id
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Unauthorized: This booking belongs to another user';
                        SET @o_old_status = @v_current_status;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Check if booking can be cancelled (only Pending or Confirmed)
                    IF @v_current_status NOT IN ('Pending', 'Confirmed')
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Cannot cancel booking with status: ' + @v_current_status;
                        SET @o_old_status = @v_current_status;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    SET @o_old_status = @v_current_status;

                    -- Cancel booking
                    UPDATE booking
                    SET status = 'Cancelled',
                        updated_at = GETDATE()
                    WHERE id = @p_booking_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Booking cancelled successfully';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_old_status = NULL;
                END CATCH
            END
        ");

        // ===========================
        // sp_booking_check_time_conflicts
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_check_time_conflicts\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_check_time_conflicts
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_check_time_conflicts
                @p_appointment_date DATE,
                @p_appointment_time TIME,
                @p_animal_ids NVARCHAR(MAX), -- Comma-separated list of animal IDs
                @p_exclude_booking_id BIGINT = NULL
            AS
            BEGIN
                SET NOCOUNT ON;

                -- Find bookings at this date/time with active status
                -- Then check which of the requested animals are already booked
                SELECT DISTINCT ab.animalID
                FROM booking b
                INNER JOIN animal_booking ab ON b.id = ab.bookingID
                WHERE b.appointment_date = @p_appointment_date
                  AND b.appointment_time = @p_appointment_time
                  AND b.status IN ('Pending', 'Confirmed')
                  AND (@p_exclude_booking_id IS NULL OR b.id != @p_exclude_booking_id)
                  AND ab.animalID IN (SELECT value FROM STRING_SPLIT(@p_animal_ids, ','));
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('danish');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_status');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_cancel');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_check_time_conflicts');
    }
};
