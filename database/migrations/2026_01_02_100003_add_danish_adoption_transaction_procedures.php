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
        // ADOPTION PROCEDURES
        // ===========================

        // sp_adoption_create
        $connection->unprepared('
            IF OBJECT_ID(\'sp_adoption_create\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_adoption_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_adoption_create
                @p_booking_id BIGINT,
                @p_transaction_id BIGINT,
                @p_animal_id BIGINT,
                @p_fee DECIMAL(10,2),
                @p_remarks NVARCHAR(MAX) = NULL,
                @o_adoption_id BIGINT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_booking_exists BIT;
                DECLARE @v_transaction_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Validate booking exists
                    SELECT @v_booking_exists = 1
                    FROM booking
                    WHERE id = @p_booking_id;

                    IF @v_booking_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Booking not found';
                        SET @o_adoption_id = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Validate transaction exists
                    SELECT @v_transaction_exists = 1
                    FROM [transaction]
                    WHERE id = @p_transaction_id;

                    IF @v_transaction_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Transaction not found';
                        SET @o_adoption_id = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Create adoption record
                    INSERT INTO adoption (bookingID, transactionID, animalID, fee, remarks, created_at, updated_at)
                    VALUES (@p_booking_id, @p_transaction_id, @p_animal_id, @p_fee, @p_remarks, GETDATE(), GETDATE());

                    SET @o_adoption_id = SCOPE_IDENTITY();
                    SET @o_status = 'success';
                    SET @o_message = 'Adoption record created successfully';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_adoption_id = NULL;
                END CATCH
            END
        ");

        // sp_adoption_read
        $connection->unprepared('
            IF OBJECT_ID(\'sp_adoption_read\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_adoption_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_adoption_read
                @p_adoption_id BIGINT
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT
                    id,
                    bookingID,
                    transactionID,
                    animalID,
                    fee,
                    remarks,
                    created_at,
                    updated_at
                FROM adoption
                WHERE id = @p_adoption_id;
            END
        ");

        // sp_adoption_get_by_booking
        $connection->unprepared('
            IF OBJECT_ID(\'sp_adoption_get_by_booking\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_adoption_get_by_booking
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_adoption_get_by_booking
                @p_booking_id BIGINT
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT
                    id,
                    bookingID,
                    transactionID,
                    animalID,
                    fee,
                    remarks,
                    created_at,
                    updated_at
                FROM adoption
                WHERE bookingID = @p_booking_id
                ORDER BY created_at DESC;
            END
        ");

        // ===========================
        // TRANSACTION PROCEDURES
        // ===========================

        // sp_transaction_create
        $connection->unprepared('
            IF OBJECT_ID(\'sp_transaction_create\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_transaction_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_create
                @p_user_id BIGINT,
                @p_amount DECIMAL(10,2),
                @p_status NVARCHAR(50),
                @p_type NVARCHAR(100),
                @p_bill_code NVARCHAR(255) = NULL,
                @p_reference_no NVARCHAR(255) = NULL,
                @p_remarks NVARCHAR(MAX) = NULL,
                @o_transaction_id BIGINT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Validation
                    IF @p_user_id IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'User ID is required';
                        SET @o_transaction_id = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    IF @p_amount IS NULL OR @p_amount < 0
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Valid amount is required';
                        SET @o_transaction_id = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Create transaction
                    INSERT INTO [transaction] (
                        userID, amount, status, type, bill_code, reference_no, remarks,
                        created_at, updated_at
                    )
                    VALUES (
                        @p_user_id, @p_amount, @p_status, @p_type, @p_bill_code, @p_reference_no, @p_remarks,
                        GETDATE(), GETDATE()
                    );

                    SET @o_transaction_id = SCOPE_IDENTITY();
                    SET @o_status = 'success';
                    SET @o_message = 'Transaction created successfully';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_transaction_id = NULL;
                END CATCH
            END
        ");

        // sp_transaction_read
        $connection->unprepared('
            IF OBJECT_ID(\'sp_transaction_read\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_transaction_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_read
                @p_transaction_id BIGINT
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT
                    id,
                    userID,
                    amount,
                    status,
                    type,
                    bill_code,
                    reference_no,
                    remarks,
                    created_at,
                    updated_at
                FROM [transaction]
                WHERE id = @p_transaction_id;
            END
        ");

        // sp_transaction_update_status
        $connection->unprepared('
            IF OBJECT_ID(\'sp_transaction_update_status\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_transaction_update_status
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_update_status
                @p_transaction_id BIGINT,
                @p_new_status NVARCHAR(50),
                @o_old_status NVARCHAR(50) OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if transaction exists and get current status
                    SELECT @v_exists = 1, @o_old_status = status
                    FROM [transaction]
                    WHERE id = @p_transaction_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Transaction not found';
                        SET @o_old_status = NULL;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Update transaction status
                    UPDATE [transaction]
                    SET status = @p_new_status,
                        updated_at = GETDATE()
                    WHERE id = @p_transaction_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Transaction status updated successfully';

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

        // sp_transaction_get_by_bill_code
        $connection->unprepared('
            IF OBJECT_ID(\'sp_transaction_get_by_bill_code\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_transaction_get_by_bill_code
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_get_by_bill_code
                @p_bill_code NVARCHAR(255)
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT
                    id,
                    userID,
                    amount,
                    status,
                    type,
                    bill_code,
                    reference_no,
                    remarks,
                    created_at,
                    updated_at
                FROM [transaction]
                WHERE bill_code = @p_bill_code;
            END
        ");

        // sp_transaction_get_by_user
        $connection->unprepared('
            IF OBJECT_ID(\'sp_transaction_get_by_user\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_transaction_get_by_user
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_transaction_get_by_user
                @p_user_id BIGINT,
                @p_status NVARCHAR(50) = NULL
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT
                    id,
                    userID,
                    amount,
                    status,
                    type,
                    bill_code,
                    reference_no,
                    remarks,
                    created_at,
                    updated_at
                FROM [transaction]
                WHERE userID = @p_user_id
                  AND (@p_status IS NULL OR status = @p_status)
                ORDER BY created_at DESC;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('danish');

        // Drop adoption procedures
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_adoption_get_by_booking');

        // Drop transaction procedures
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_update_status');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_bill_code');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_transaction_get_by_user');
    }
};
