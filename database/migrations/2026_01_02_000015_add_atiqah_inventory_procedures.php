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
        // sp_inventory_create
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_inventory_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_inventory_create(
                IN p_slot_id BIGINT,
                IN p_item_name VARCHAR(255),
                IN p_category_id BIGINT,
                IN p_quantity INT,
                IN p_weight DECIMAL(10,2),
                IN p_brand VARCHAR(255),
                IN p_status VARCHAR(50),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_inventory_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_slot_exists INT;
                DECLARE v_category_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_inventory_id = NULL;
                END;

                START TRANSACTION;

                -- Validation
                IF p_slot_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Slot ID is required';
                    SET o_inventory_id = NULL;
                    ROLLBACK;
                ELSEIF p_item_name IS NULL OR TRIM(p_item_name) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Item name is required';
                    SET o_inventory_id = NULL;
                    ROLLBACK;
                ELSEIF p_category_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Category ID is required';
                    SET o_inventory_id = NULL;
                    ROLLBACK;
                ELSEIF p_quantity IS NULL OR p_quantity < 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Quantity must be 0 or greater';
                    SET o_inventory_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Check if slot exists
                    SELECT COUNT(*) INTO v_slot_exists
                    FROM slot
                    WHERE id = p_slot_id;

                    IF v_slot_exists = 0 THEN
                        SET o_status = 'error';
                        SET o_message = 'The selected slot does not exist';
                        SET o_inventory_id = NULL;
                        ROLLBACK;
                    ELSE
                        -- Check if category exists
                        SELECT COUNT(*) INTO v_category_exists
                        FROM category
                        WHERE id = p_category_id;

                        IF v_category_exists = 0 THEN
                            SET o_status = 'error';
                            SET o_message = 'The selected category does not exist';
                            SET o_inventory_id = NULL;
                            ROLLBACK;
                        ELSE
                            -- Insert inventory
                            INSERT INTO inventory (
                                slotID, item_name, categoryID, quantity, weight, brand, status,
                                created_at, updated_at
                            )
                            VALUES (
                                p_slot_id, p_item_name, p_category_id, p_quantity,
                                p_weight, p_brand, COALESCE(p_status, 'available'),
                                NOW(), NOW()
                            );

                            SET o_inventory_id = LAST_INSERT_ID();
                            SET o_status = 'success';
                            SET o_message = 'Inventory item created successfully';
                            COMMIT;
                        END IF;
                    END IF;
                END IF;
            END
        ");

        // ===========================
        // sp_inventory_read
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_inventory_read
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_inventory_read(
                IN p_inventory_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    slotID,
                    item_name,
                    categoryID,
                    quantity,
                    weight,
                    brand,
                    status,
                    created_at,
                    updated_at
                FROM inventory
                WHERE id = p_inventory_id;
            END
        ");

        // ===========================
        // sp_inventory_update
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_inventory_update
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_inventory_update(
                IN p_inventory_id BIGINT,
                IN p_item_name VARCHAR(255),
                IN p_category_id BIGINT,
                IN p_quantity INT,
                IN p_weight DECIMAL(10,2),
                IN p_brand VARCHAR(255),
                IN p_status VARCHAR(50),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_category_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if inventory exists
                SELECT COUNT(*) INTO v_exists FROM inventory WHERE id = p_inventory_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Inventory item not found';
                    ROLLBACK;
                ELSEIF p_item_name IS NULL OR TRIM(p_item_name) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Item name is required';
                    ROLLBACK;
                ELSEIF p_category_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Category ID is required';
                    ROLLBACK;
                ELSEIF p_quantity IS NULL OR p_quantity < 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Quantity must be 0 or greater';
                    ROLLBACK;
                ELSE
                    -- Check if category exists
                    SELECT COUNT(*) INTO v_category_exists
                    FROM category
                    WHERE id = p_category_id;

                    IF v_category_exists = 0 THEN
                        SET o_status = 'error';
                        SET o_message = 'The selected category does not exist';
                        ROLLBACK;
                    ELSE
                        -- Update inventory
                        UPDATE inventory
                        SET item_name = p_item_name,
                            categoryID = p_category_id,
                            quantity = p_quantity,
                            weight = p_weight,
                            brand = p_brand,
                            status = p_status,
                            updated_at = NOW()
                        WHERE id = p_inventory_id;

                        SET o_status = 'success';
                        SET o_message = 'Inventory item updated successfully';
                        COMMIT;
                    END IF;
                END IF;
            END
        ");

        // ===========================
        // sp_inventory_delete
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_inventory_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_inventory_delete(
                IN p_inventory_id BIGINT,
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

                -- Check if inventory exists
                SELECT COUNT(*) INTO v_exists FROM inventory WHERE id = p_inventory_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Inventory item not found';
                    ROLLBACK;
                ELSE
                    -- Delete inventory
                    DELETE FROM inventory WHERE id = p_inventory_id;

                    SET o_status = 'success';
                    SET o_message = 'Inventory item deleted successfully';
                    COMMIT;
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

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_inventory_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_inventory_read');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_inventory_update');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_inventory_delete');
    }
};
