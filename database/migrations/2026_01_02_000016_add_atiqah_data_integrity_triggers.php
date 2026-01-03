<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = DB::connection('atiqah');

        // ===========================
        // Create audit_log table
        // ===========================
        Schema::connection('atiqah')->create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50);
            $table->string('operation', 10); // INSERT, UPDATE, DELETE
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('table_name');
            $table->index('operation');
            $table->index('record_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        // ===========================
        // SECTION TRIGGERS
        // ===========================

        // Section - After Insert
        $connection->unprepared('
            DROP TRIGGER IF EXISTS section_after_insert
        ');

        $connection->unprepared("
            CREATE TRIGGER section_after_insert
            AFTER INSERT ON section
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'section', 'INSERT', NEW.id,
                    CONCAT('name: ', NEW.name, ', description: ', NEW.description),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Section - After Update
        $connection->unprepared('
            DROP TRIGGER IF EXISTS section_after_update
        ');

        $connection->unprepared("
            CREATE TRIGGER section_after_update
            AFTER UPDATE ON section
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'section', 'UPDATE', NEW.id,
                    CONCAT('name: ', OLD.name, ', description: ', OLD.description),
                    CONCAT('name: ', NEW.name, ', description: ', NEW.description),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Section - After Delete
        $connection->unprepared('
            DROP TRIGGER IF EXISTS section_after_delete
        ');

        $connection->unprepared("
            CREATE TRIGGER section_after_delete
            AFTER DELETE ON section
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'section', 'DELETE', OLD.id,
                    CONCAT('name: ', OLD.name, ', description: ', OLD.description),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // ===========================
        // SLOT TRIGGERS
        // ===========================

        // Slot - After Insert
        $connection->unprepared('
            DROP TRIGGER IF EXISTS slot_after_insert
        ');

        $connection->unprepared("
            CREATE TRIGGER slot_after_insert
            AFTER INSERT ON slot
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'slot', 'INSERT', NEW.id,
                    CONCAT('name: ', NEW.name, ', sectionID: ', NEW.sectionID, ', capacity: ', NEW.capacity, ', status: ', NEW.status),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Slot - After Update
        $connection->unprepared('
            DROP TRIGGER IF EXISTS slot_after_update
        ');

        $connection->unprepared("
            CREATE TRIGGER slot_after_update
            AFTER UPDATE ON slot
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'slot', 'UPDATE', NEW.id,
                    CONCAT('name: ', OLD.name, ', sectionID: ', OLD.sectionID, ', capacity: ', OLD.capacity, ', status: ', OLD.status),
                    CONCAT('name: ', NEW.name, ', sectionID: ', NEW.sectionID, ', capacity: ', NEW.capacity, ', status: ', NEW.status),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Slot - After Delete
        $connection->unprepared('
            DROP TRIGGER IF EXISTS slot_after_delete
        ');

        $connection->unprepared("
            CREATE TRIGGER slot_after_delete
            AFTER DELETE ON slot
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'slot', 'DELETE', OLD.id,
                    CONCAT('name: ', OLD.name, ', sectionID: ', OLD.sectionID, ', capacity: ', OLD.capacity, ', status: ', OLD.status),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // ===========================
        // CATEGORY TRIGGERS
        // ===========================

        // Category - After Insert
        $connection->unprepared('
            DROP TRIGGER IF EXISTS category_after_insert
        ');

        $connection->unprepared("
            CREATE TRIGGER category_after_insert
            AFTER INSERT ON category
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'category', 'INSERT', NEW.id,
                    CONCAT('main: ', NEW.main, ', sub: ', NEW.sub),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Category - After Update
        $connection->unprepared('
            DROP TRIGGER IF EXISTS category_after_update
        ');

        $connection->unprepared("
            CREATE TRIGGER category_after_update
            AFTER UPDATE ON category
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'category', 'UPDATE', NEW.id,
                    CONCAT('main: ', OLD.main, ', sub: ', OLD.sub),
                    CONCAT('main: ', NEW.main, ', sub: ', NEW.sub),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Category - After Delete
        $connection->unprepared('
            DROP TRIGGER IF EXISTS category_after_delete
        ');

        $connection->unprepared("
            CREATE TRIGGER category_after_delete
            AFTER DELETE ON category
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'category', 'DELETE', OLD.id,
                    CONCAT('main: ', OLD.main, ', sub: ', OLD.sub),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // ===========================
        // INVENTORY TRIGGERS
        // ===========================

        // Inventory - After Insert
        $connection->unprepared('
            DROP TRIGGER IF EXISTS inventory_after_insert
        ');

        $connection->unprepared("
            CREATE TRIGGER inventory_after_insert
            AFTER INSERT ON inventory
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'inventory', 'INSERT', NEW.id,
                    CONCAT('item_name: ', NEW.item_name, ', slotID: ', NEW.slotID, ', categoryID: ', NEW.categoryID, ', quantity: ', NEW.quantity, ', status: ', NEW.status),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Inventory - After Update
        $connection->unprepared('
            DROP TRIGGER IF EXISTS inventory_after_update
        ');

        $connection->unprepared("
            CREATE TRIGGER inventory_after_update
            AFTER UPDATE ON inventory
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values, new_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'inventory', 'UPDATE', NEW.id,
                    CONCAT('item_name: ', OLD.item_name, ', slotID: ', OLD.slotID, ', categoryID: ', OLD.categoryID, ', quantity: ', OLD.quantity, ', status: ', OLD.status),
                    CONCAT('item_name: ', NEW.item_name, ', slotID: ', NEW.slotID, ', categoryID: ', NEW.categoryID, ', quantity: ', NEW.quantity, ', status: ', NEW.status),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // Inventory - After Delete
        $connection->unprepared('
            DROP TRIGGER IF EXISTS inventory_after_delete
        ');

        $connection->unprepared("
            CREATE TRIGGER inventory_after_delete
            AFTER DELETE ON inventory
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (
                    table_name, operation, record_id, old_values,
                    user_id, user_name, user_email, user_role
                )
                VALUES (
                    'inventory', 'DELETE', OLD.id,
                    CONCAT('item_name: ', OLD.item_name, ', slotID: ', OLD.slotID, ', categoryID: ', OLD.categoryID, ', quantity: ', OLD.quantity, ', status: ', OLD.status),
                    @audit_user_id, @audit_user_name, @audit_user_email, @audit_user_role
                );
            END
        ");

        // ===========================
        // VALIDATION TRIGGERS
        // ===========================

        // Slot - Before Insert/Update - Validate capacity
        $connection->unprepared('
            DROP TRIGGER IF EXISTS slot_before_insert_validate
        ');

        $connection->unprepared("
            CREATE TRIGGER slot_before_insert_validate
            BEFORE INSERT ON slot
            FOR EACH ROW
            BEGIN
                IF NEW.capacity < 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Slot capacity must be at least 1';
                END IF;
            END
        ");

        $connection->unprepared('
            DROP TRIGGER IF EXISTS slot_before_update_validate
        ');

        $connection->unprepared("
            CREATE TRIGGER slot_before_update_validate
            BEFORE UPDATE ON slot
            FOR EACH ROW
            BEGIN
                IF NEW.capacity < 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Slot capacity must be at least 1';
                END IF;
            END
        ");

        // Inventory - Before Insert/Update - Validate quantity
        $connection->unprepared('
            DROP TRIGGER IF EXISTS inventory_before_insert_validate
        ');

        $connection->unprepared("
            CREATE TRIGGER inventory_before_insert_validate
            BEFORE INSERT ON inventory
            FOR EACH ROW
            BEGIN
                IF NEW.quantity < 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Inventory quantity cannot be negative';
                END IF;
            END
        ");

        $connection->unprepared('
            DROP TRIGGER IF EXISTS inventory_before_update_validate
        ');

        $connection->unprepared("
            CREATE TRIGGER inventory_before_update_validate
            BEFORE UPDATE ON inventory
            FOR EACH ROW
            BEGIN
                IF NEW.quantity < 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Inventory quantity cannot be negative';
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

        // Drop all triggers
        $connection->unprepared('DROP TRIGGER IF EXISTS section_after_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS section_after_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS section_after_delete');

        $connection->unprepared('DROP TRIGGER IF EXISTS slot_after_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS slot_after_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS slot_after_delete');
        $connection->unprepared('DROP TRIGGER IF EXISTS slot_before_insert_validate');
        $connection->unprepared('DROP TRIGGER IF EXISTS slot_before_update_validate');

        $connection->unprepared('DROP TRIGGER IF EXISTS category_after_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS category_after_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS category_after_delete');

        $connection->unprepared('DROP TRIGGER IF EXISTS inventory_after_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS inventory_after_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS inventory_after_delete');
        $connection->unprepared('DROP TRIGGER IF EXISTS inventory_before_insert_validate');
        $connection->unprepared('DROP TRIGGER IF EXISTS inventory_before_update_validate');

        // Drop audit_log table
        Schema::connection('atiqah')->dropIfExists('audit_log');
    }
};
