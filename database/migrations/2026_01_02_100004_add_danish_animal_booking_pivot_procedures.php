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
        // sp_booking_attach_animals
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_attach_animals\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_attach_animals
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_attach_animals
                @p_booking_id BIGINT,
                @p_animal_ids NVARCHAR(MAX), -- Comma-separated list: 'id1:remarks1,id2:remarks2'
                @o_attached_count INT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_booking_exists BIT;
                DECLARE @v_animal_id BIGINT;
                DECLARE @v_remarks NVARCHAR(500);
                DECLARE @v_pair NVARCHAR(MAX);
                DECLARE @v_pos INT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if booking exists
                    SELECT @v_booking_exists = 1
                    FROM booking
                    WHERE id = @p_booking_id;

                    IF @v_booking_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Booking not found';
                        SET @o_attached_count = 0;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    SET @o_attached_count = 0;

                    -- Parse and insert each animal
                    DECLARE animal_cursor CURSOR FOR
                        SELECT value FROM STRING_SPLIT(@p_animal_ids, ',');

                    OPEN animal_cursor;
                    FETCH NEXT FROM animal_cursor INTO @v_pair;

                    WHILE @@FETCH_STATUS = 0
                    BEGIN
                        -- Parse 'animalId:remarks' format
                        SET @v_pos = CHARINDEX(':', @v_pair);

                        IF @v_pos > 0
                        BEGIN
                            SET @v_animal_id = CAST(LEFT(@v_pair, @v_pos - 1) AS BIGINT);
                            SET @v_remarks = NULLIF(LTRIM(RTRIM(SUBSTRING(@v_pair, @v_pos + 1, LEN(@v_pair)))), '');
                        END
                        ELSE
                        BEGIN
                            SET @v_animal_id = CAST(@v_pair AS BIGINT);
                            SET @v_remarks = NULL;
                        END

                        -- Insert into pivot table (skip duplicates)
                        IF NOT EXISTS (SELECT 1 FROM animal_booking WHERE bookingID = @p_booking_id AND animalID = @v_animal_id)
                        BEGIN
                            INSERT INTO animal_booking (bookingID, animalID, remarks, created_at, updated_at)
                            VALUES (@p_booking_id, @v_animal_id, @v_remarks, GETDATE(), GETDATE());

                            SET @o_attached_count = @o_attached_count + 1;
                        END

                        FETCH NEXT FROM animal_cursor INTO @v_pair;
                    END

                    CLOSE animal_cursor;
                    DEALLOCATE animal_cursor;

                    SET @o_status = 'success';
                    SET @o_message = CAST(@o_attached_count AS NVARCHAR(10)) + ' animal(s) attached to booking';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    IF CURSOR_STATUS('local', 'animal_cursor') >= 0
                    BEGIN
                        CLOSE animal_cursor;
                        DEALLOCATE animal_cursor;
                    END

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_attached_count = 0;
                END CATCH
            END
        ");

        // ===========================
        // sp_booking_detach_animals
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_detach_animals\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_detach_animals
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_detach_animals
                @p_booking_id BIGINT,
                @p_animal_ids NVARCHAR(MAX) = NULL, -- Comma-separated animal IDs, or NULL to detach all
                @o_detached_count INT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_booking_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if booking exists
                    SELECT @v_booking_exists = 1
                    FROM booking
                    WHERE id = @p_booking_id;

                    IF @v_booking_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Booking not found';
                        SET @o_detached_count = 0;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    IF @p_animal_ids IS NULL
                    BEGIN
                        -- Detach all animals
                        DELETE FROM animal_booking
                        WHERE bookingID = @p_booking_id;
                    END
                    ELSE
                    BEGIN
                        -- Detach specific animals
                        DELETE FROM animal_booking
                        WHERE bookingID = @p_booking_id
                          AND animalID IN (SELECT value FROM STRING_SPLIT(@p_animal_ids, ','));
                    END

                    SET @o_detached_count = @@ROWCOUNT;
                    SET @o_status = 'success';
                    SET @o_message = CAST(@o_detached_count AS NVARCHAR(10)) + ' animal(s) detached from booking';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_detached_count = 0;
                END CATCH
            END
        ");

        // ===========================
        // sp_booking_get_animals
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_get_animals\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_get_animals
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_get_animals
                @p_booking_id BIGINT
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT
                    animalID,
                    remarks,
                    created_at,
                    updated_at
                FROM animal_booking
                WHERE bookingID = @p_booking_id
                ORDER BY created_at;
            END
        ");

        // ===========================
        // sp_booking_update_animal_remarks
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_update_animal_remarks\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_update_animal_remarks
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_update_animal_remarks
                @p_booking_id BIGINT,
                @p_animal_id BIGINT,
                @p_remarks NVARCHAR(500),
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if the animal-booking relationship exists
                    SELECT @v_exists = 1
                    FROM animal_booking
                    WHERE bookingID = @p_booking_id AND animalID = @p_animal_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Animal not found in this booking';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Update remarks
                    UPDATE animal_booking
                    SET remarks = @p_remarks,
                        updated_at = GETDATE()
                    WHERE bookingID = @p_booking_id AND animalID = @p_animal_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Remarks updated successfully';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                END CATCH
            END
        ");

        // ===========================
        // sp_booking_get_animal_count
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_booking_get_animal_count\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_booking_get_animal_count
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_booking_get_animal_count
                @p_booking_id BIGINT,
                @o_count INT OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT @o_count = COUNT(*)
                FROM animal_booking
                WHERE bookingID = @p_booking_id;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('danish');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_attach_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_detach_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_update_animal_remarks');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_booking_get_animal_count');
    }
};
