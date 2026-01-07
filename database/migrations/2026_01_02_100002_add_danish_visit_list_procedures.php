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
        // sp_visit_list_get_or_create
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_visit_list_get_or_create\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_visit_list_get_or_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_get_or_create
                @p_user_id BIGINT,
                @o_list_id BIGINT OUTPUT,
                @o_is_new BIT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if visit list exists for this user
                    SELECT @o_list_id = id
                    FROM visit_list
                    WHERE userID = @p_user_id;

                    IF @o_list_id IS NULL
                    BEGIN
                        -- Create new visit list
                        INSERT INTO visit_list (userID, created_at, updated_at)
                        VALUES (@p_user_id, GETDATE(), GETDATE());

                        SET @o_list_id = SCOPE_IDENTITY();
                        SET @o_is_new = 1;
                        SET @o_message = 'Visit list created successfully';
                    END
                    ELSE
                    BEGIN
                        SET @o_is_new = 0;
                        SET @o_message = 'Visit list already exists';
                    END

                    SET @o_status = 'success';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_list_id = NULL;
                    SET @o_is_new = 0;
                END CATCH
            END
        ");

        // ===========================
        // sp_visit_list_add_animal
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_visit_list_add_animal\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_visit_list_add_animal
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_add_animal
                @p_list_id BIGINT,
                @p_animal_id BIGINT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;
                DECLARE @v_duplicate BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if visit list exists
                    SELECT @v_exists = 1
                    FROM visit_list
                    WHERE id = @p_list_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Visit list not found';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Check for duplicate (animal already in this visit list)
                    SELECT @v_duplicate = 1
                    FROM visit_list_animal
                    WHERE listID = @p_list_id AND animalID = @p_animal_id;

                    IF @v_duplicate = 1
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'This animal is already in your visit list';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Add animal to visit list
                    INSERT INTO visit_list_animal (listID, animalID, created_at, updated_at)
                    VALUES (@p_list_id, @p_animal_id, GETDATE(), GETDATE());

                    -- Update visit list timestamp
                    UPDATE visit_list
                    SET updated_at = GETDATE()
                    WHERE id = @p_list_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Animal added to visit list successfully';

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
        // sp_visit_list_remove_animal
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_visit_list_remove_animal\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_visit_list_remove_animal
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_remove_animal
                @p_list_id BIGINT,
                @p_animal_id BIGINT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;
                DECLARE @v_animal_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if visit list exists
                    SELECT @v_exists = 1
                    FROM visit_list
                    WHERE id = @p_list_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Visit list not found';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Check if animal is in the visit list
                    SELECT @v_animal_exists = 1
                    FROM visit_list_animal
                    WHERE listID = @p_list_id AND animalID = @p_animal_id;

                    IF @v_animal_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Animal not found in visit list';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Remove animal from visit list
                    DELETE FROM visit_list_animal
                    WHERE listID = @p_list_id AND animalID = @p_animal_id;

                    -- Update visit list timestamp
                    UPDATE visit_list
                    SET updated_at = GETDATE()
                    WHERE id = @p_list_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Animal removed from visit list successfully';

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
        // sp_visit_list_get_animals
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_visit_list_get_animals\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_visit_list_get_animals
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_get_animals
                @p_list_id BIGINT
            AS
            BEGIN
                SET NOCOUNT ON;

                SELECT animalID, created_at, updated_at
                FROM visit_list_animal
                WHERE listID = @p_list_id
                ORDER BY created_at DESC;
            END
        ");

        // ===========================
        // sp_visit_list_delete
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_visit_list_delete\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_visit_list_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_delete
                @p_list_id BIGINT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if visit list exists
                    SELECT @v_exists = 1
                    FROM visit_list
                    WHERE id = @p_list_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Visit list not found';
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Delete all animals from the visit list first (pivot table)
                    DELETE FROM visit_list_animal
                    WHERE listID = @p_list_id;

                    -- Delete the visit list
                    DELETE FROM visit_list
                    WHERE id = @p_list_id;

                    SET @o_status = 'success';
                    SET @o_message = 'Visit list deleted successfully';

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
        // sp_visit_list_clear_animals
        // ===========================
        $connection->unprepared('
            IF OBJECT_ID(\'sp_visit_list_clear_animals\', \'P\') IS NOT NULL
                DROP PROCEDURE sp_visit_list_clear_animals
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_clear_animals
                @p_list_id BIGINT,
                @p_animal_ids NVARCHAR(MAX), -- Comma-separated list of animal IDs to remove
                @o_removed_count INT OUTPUT,
                @o_status NVARCHAR(20) OUTPUT,
                @o_message NVARCHAR(MAX) OUTPUT
            AS
            BEGIN
                SET NOCOUNT ON;

                DECLARE @v_exists BIT;

                BEGIN TRY
                    BEGIN TRANSACTION;

                    -- Check if visit list exists
                    SELECT @v_exists = 1
                    FROM visit_list
                    WHERE id = @p_list_id;

                    IF @v_exists IS NULL
                    BEGIN
                        SET @o_status = 'error';
                        SET @o_message = 'Visit list not found';
                        SET @o_removed_count = 0;
                        ROLLBACK TRANSACTION;
                        RETURN;
                    END

                    -- Remove specified animals from visit list
                    DELETE FROM visit_list_animal
                    WHERE listID = @p_list_id
                      AND animalID IN (SELECT value FROM STRING_SPLIT(@p_animal_ids, ','));

                    SET @o_removed_count = @@ROWCOUNT;

                    -- Update visit list timestamp
                    UPDATE visit_list
                    SET updated_at = GETDATE()
                    WHERE id = @p_list_id;

                    SET @o_status = 'success';
                    SET @o_message = CAST(@o_removed_count AS NVARCHAR(10)) + ' animal(s) removed from visit list';

                    COMMIT TRANSACTION;
                END TRY
                BEGIN CATCH
                    IF @@TRANCOUNT > 0
                        ROLLBACK TRANSACTION;

                    SET @o_status = 'error';
                    SET @o_message = ERROR_MESSAGE();
                    SET @o_removed_count = 0;
                END CATCH
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('danish');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_or_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_add_animal');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_remove_animal');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_delete');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_clear_animals');
    }
};
