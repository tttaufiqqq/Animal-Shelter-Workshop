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
        $connection = DB::connection('atiqah');

        // ===========================
        // sp_category_create
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_category_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_category_create(
                IN p_main VARCHAR(255),
                IN p_sub VARCHAR(255),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_category_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_category_id = NULL;
                END;

                START TRANSACTION;

                -- Validation
                IF p_main IS NULL OR TRIM(p_main) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Main category is required';
                    SET o_category_id = NULL;
                    ROLLBACK;
                ELSEIF p_sub IS NULL OR TRIM(p_sub) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Sub category is required';
                    SET o_category_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Insert category
                    INSERT INTO category (
                        main, sub, created_at, updated_at
                    )
                    VALUES (
                        p_main, p_sub, NOW(), NOW()
                    );

                    SET o_category_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Category created successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_category_read
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_category_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_category_read(
                IN p_category_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    main,
                    sub,
                    created_at,
                    updated_at
                FROM category
                WHERE id = p_category_id;
            END
        ");

        // ===========================
        // sp_category_update
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_category_update
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_category_update(
                IN p_category_id BIGINT,
                IN p_main VARCHAR(255),
                IN p_sub VARCHAR(255),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if category exists
                SELECT COUNT(*) INTO v_exists FROM category WHERE id = p_category_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Category not found';
                    ROLLBACK;
                ELSEIF p_main IS NULL OR TRIM(p_main) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Main category is required';
                    ROLLBACK;
                ELSEIF p_sub IS NULL OR TRIM(p_sub) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Sub category is required';
                    ROLLBACK;
                ELSE
                    -- Update category
                    UPDATE category
                    SET main = p_main,
                        sub = p_sub,
                        updated_at = NOW()
                    WHERE id = p_category_id;

                    SET o_status = 'success';
                    SET o_message = 'Category updated successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_category_delete
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_category_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_category_delete(
                IN p_category_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_has_inventories BOOLEAN,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_inventory_count INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_has_inventories = FALSE;
                END;

                START TRANSACTION;

                -- Check if category exists
                SELECT COUNT(*) INTO v_exists FROM category WHERE id = p_category_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Category not found';
                    SET o_has_inventories = FALSE;
                    ROLLBACK;
                ELSE
                    -- Check if category has inventories
                    SELECT COUNT(*) INTO v_inventory_count
                    FROM inventory
                    WHERE categoryID = p_category_id;

                    IF v_inventory_count > 0 THEN
                        -- Don't delete, just flag it
                        SET o_status = 'error';
                        SET o_message = 'Cannot delete category with existing inventory items. Please delete or reassign the inventory items first.';
                        SET o_has_inventories = TRUE;
                        ROLLBACK;
                    ELSE
                        -- Safe to delete
                        DELETE FROM category WHERE id = p_category_id;

                        SET o_status = 'success';
                        SET o_message = 'Category deleted successfully';
                        SET o_has_inventories = FALSE;
                        COMMIT;
                    END IF;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('atiqah');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_category_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_category_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_category_update');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_category_delete');
    }
};
