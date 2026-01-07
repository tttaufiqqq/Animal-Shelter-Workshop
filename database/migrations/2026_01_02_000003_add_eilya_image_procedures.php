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
        // sp_image_create
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_image_create
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_image_create(
                IN p_image_path VARCHAR(255),
                IN p_report_id BIGINT,
                IN p_animal_id BIGINT,
                IN p_clinic_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_image_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_image_id = NULL;
                END;

                START TRANSACTION;

                -- Validation: at least one parent must be provided
                IF p_report_id IS NULL AND p_animal_id IS NULL AND p_clinic_id IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Image must be associated with a report, animal, or clinic';
                    SET o_image_id = NULL;
                    ROLLBACK;
                ELSEIF p_image_path IS NULL OR TRIM(p_image_path) = '' THEN
                    SET o_status = 'error';
                    SET o_message = 'Image path is required';
                    SET o_image_id = NULL;
                    ROLLBACK;
                ELSE
                    -- Insert image (trigger will validate reportID exists)
                    INSERT INTO image (
                        image_path, reportID, animalID, clinicID,
                        created_at, updated_at
                    )
                    VALUES (
                        p_image_path, p_report_id, p_animal_id, p_clinic_id,
                        NOW(), NOW()
                    );

                    SET o_image_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Image created successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_image_delete
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_image_delete
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_image_delete(
                IN p_image_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_image_path VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_exists INT;
                DECLARE v_image_path VARCHAR(255);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_image_path = NULL;
                END;

                START TRANSACTION;

                -- Check if image exists and get path
                SELECT COUNT(*), MAX(image_path)
                INTO v_exists, v_image_path
                FROM image
                WHERE id = p_image_id;

                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Image not found';
                    SET o_image_path = NULL;
                    ROLLBACK;
                ELSE
                    -- Delete image
                    DELETE FROM image WHERE id = p_image_id;

                    SET o_image_path = v_image_path;
                    SET o_status = 'success';
                    SET o_message = 'Image deleted successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_image_read_by_report
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_image_read_by_report
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_image_read_by_report(
                IN p_report_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    image_path,
                    reportID,
                    animalID,
                    clinicID,
                    created_at,
                    updated_at
                FROM image
                WHERE reportID = p_report_id
                ORDER BY created_at;
            END
        ");

        // ===========================
        // sp_image_read_by_animal
        // ===========================
        $connection->unprepared('
            DROP PROCEDURE IF EXISTS sp_image_read_by_animal
        ');

        $connection->unprepared("
            CREATE PROCEDURE sp_image_read_by_animal(
                IN p_animal_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    image_path,
                    reportID,
                    animalID,
                    clinicID,
                    created_at,
                    updated_at
                FROM image
                WHERE animalID = p_animal_id
                ORDER BY created_at;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection('eilya');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_image_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_image_delete');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_image_read_by_report');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_image_read_by_animal');
    }
};
