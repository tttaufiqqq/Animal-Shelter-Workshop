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
        $connection = DB::connection('eilya');

        // ===========================
        // RESCUE BEFORE INSERT TRIGGER
        // ===========================
        $connection->unprepared("
            CREATE TRIGGER trg_rescue_before_insert
            BEFORE INSERT ON rescue
            FOR EACH ROW
            BEGIN
                -- Validate reportID exists
                IF NOT EXISTS (SELECT 1 FROM report WHERE id = NEW.reportID) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot create rescue: Report does not exist';
                END IF;

                -- Set default status if NULL
                IF NEW.status IS NULL OR TRIM(NEW.status) = '' THEN
                    SET NEW.status = 'Scheduled';
                END IF;

                -- Set default priority if NULL
                IF NEW.priority IS NULL OR TRIM(NEW.priority) = '' THEN
                    SET NEW.priority = 'normal';
                END IF;
            END
        ");

        // ===========================
        // RESCUE AFTER INSERT TRIGGER
        // ⭐ CRITICAL: Auto-sync report status to 'Assigned'
        // ===========================
        $connection->unprepared("
            CREATE TRIGGER trg_rescue_after_insert
            AFTER INSERT ON rescue
            FOR EACH ROW
            BEGIN
                -- Auto-sync report status to 'Assigned' when rescue is created
                UPDATE report
                SET report_status = 'Assigned',
                    updated_at = NOW()
                WHERE id = NEW.reportID;
            END
        ");

        // ===========================
        // RESCUE BEFORE UPDATE TRIGGER
        // ===========================
        $connection->unprepared("
            CREATE TRIGGER trg_rescue_before_update
            BEFORE UPDATE ON rescue
            FOR EACH ROW
            BEGIN
                -- Prevent updates to completed rescues
                IF OLD.status IN ('Success', 'Failed') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot update completed rescue (status: Success/Failed)';
                END IF;

                -- Require remarks for Success/Failed status
                IF NEW.status IN ('Success', 'Failed')
                   AND (NEW.remarks IS NULL OR TRIM(NEW.remarks) = '') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Remarks are required when marking rescue as Success or Failed';
                END IF;
            END
        ");

        // ===========================
        // RESCUE AFTER UPDATE TRIGGER
        // ⭐ CRITICAL: Auto-sync report status based on rescue status
        // ===========================
        $connection->unprepared("
            CREATE TRIGGER trg_rescue_after_update
            AFTER UPDATE ON rescue
            FOR EACH ROW
            BEGIN
                -- Sync: Rescue 'In Progress' → Report 'In Progress'
                IF NEW.status = 'In Progress' AND OLD.status != 'In Progress' THEN
                    UPDATE report
                    SET report_status = 'In Progress',
                        updated_at = NOW()
                    WHERE id = NEW.reportID;
                END IF;

                -- Sync: Rescue 'Success' or 'Failed' → Report 'Completed'
                IF NEW.status IN ('Success', 'Failed')
                   AND OLD.status NOT IN ('Success', 'Failed') THEN
                    UPDATE report
                    SET report_status = 'Completed',
                        updated_at = NOW()
                    WHERE id = NEW.reportID;
                END IF;
            END
        ");

        // ===========================
        // IMAGE BEFORE INSERT TRIGGER
        // ===========================
        $connection->unprepared("
            CREATE TRIGGER trg_image_before_insert
            BEFORE INSERT ON image
            FOR EACH ROW
            BEGIN
                -- Require at least one parent (reportID, animalID, or clinicID)
                IF NEW.reportID IS NULL AND NEW.animalID IS NULL AND NEW.clinicID IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Image must be associated with a report, animal, or clinic';
                END IF;

                -- Validate reportID exists (same database - can validate)
                IF NEW.reportID IS NOT NULL THEN
                    IF NOT EXISTS (SELECT 1 FROM report WHERE id = NEW.reportID) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Cannot create image: Report does not exist';
                    END IF;
                END IF;

                -- animalID and clinicID are cross-database, validated in application layer
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('eilya');

        $connection->unprepared('DROP TRIGGER IF EXISTS trg_rescue_before_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_rescue_after_insert');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_rescue_before_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_rescue_after_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS trg_image_before_insert');
    }
};
