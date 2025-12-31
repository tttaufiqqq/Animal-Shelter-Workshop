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
        $connection = DB::connection('shafiqah');

        // ===========================
        // sp_clinic_create
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_clinic_create
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_clinic_create(
                IN p_name VARCHAR(255),
                IN p_address VARCHAR(500),
                IN p_contact_num VARCHAR(30),
                IN p_latitude DECIMAL(10,8),
                IN p_longitude DECIMAL(11,8),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_clinic_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_clinic_id = NULL;
                END;

                START TRANSACTION;

                -- Insert clinic
                INSERT INTO clinic (name, address, contactNum, latitude, longitude, created_at, updated_at)
                VALUES (p_name, p_address, p_contact_num, p_latitude, p_longitude, NOW(), NOW());

                SET o_clinic_id = LAST_INSERT_ID();
                SET o_status = 'success';
                SET o_message = CONCAT('Clinic \"', p_name, '\" created successfully');

                COMMIT;
            END
        ");

        // ===========================
        // sp_clinic_read
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_clinic_read
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_clinic_read(
                IN p_clinic_id BIGINT
            )
            BEGIN
                SELECT
                    id,
                    name,
                    address,
                    contactNum,
                    latitude,
                    longitude,
                    created_at,
                    updated_at
                FROM clinic
                WHERE id = p_clinic_id;
            END
        ");

        // ===========================
        // sp_clinic_read_all
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_clinic_read_all
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_clinic_read_all()
            BEGIN
                SELECT
                    c.id,
                    c.name,
                    c.address,
                    c.contactNum,
                    c.latitude,
                    c.longitude,
                    c.created_at,
                    c.updated_at,
                    COUNT(v.id) as vet_count
                FROM clinic c
                LEFT JOIN vet v ON c.id = v.clinicID
                GROUP BY c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude, c.created_at, c.updated_at
                ORDER BY c.name;
            END
        ");

        // ===========================
        // sp_clinic_update
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_clinic_update
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_clinic_update(
                IN p_clinic_id BIGINT,
                IN p_name VARCHAR(255),
                IN p_address VARCHAR(500),
                IN p_contact_num VARCHAR(30),
                IN p_latitude DECIMAL(10,8),
                IN p_longitude DECIMAL(11,8),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if clinic exists
                IF NOT EXISTS (SELECT 1 FROM clinic WHERE id = p_clinic_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Clinic not found';
                    ROLLBACK;
                ELSE
                    UPDATE clinic
                    SET
                        name = p_name,
                        address = p_address,
                        contactNum = p_contact_num,
                        latitude = p_latitude,
                        longitude = p_longitude,
                        updated_at = NOW()
                    WHERE id = p_clinic_id;

                    SET o_status = 'success';
                    SET o_message = CONCAT('Clinic \"', p_name, '\" updated successfully');
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_clinic_delete
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_clinic_delete
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_clinic_delete(
                IN p_clinic_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_vet_count INT;
                DECLARE v_clinic_name VARCHAR(255);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if clinic exists
                SELECT name INTO v_clinic_name FROM clinic WHERE id = p_clinic_id;

                IF v_clinic_name IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Clinic not found';
                    ROLLBACK;
                ELSE
                    -- Check for associated vets (referential integrity)
                    SELECT COUNT(*) INTO v_vet_count FROM vet WHERE clinicID = p_clinic_id;

                    IF v_vet_count > 0 THEN
                        SET o_status = 'error';
                        SET o_message = CONCAT('Cannot delete clinic with ', v_vet_count, ' associated veterinarians. Please reassign or remove vets first.');
                        ROLLBACK;
                    ELSE
                        DELETE FROM clinic WHERE id = p_clinic_id;

                        SET o_status = 'success';
                        SET o_message = CONCAT('Clinic \"', v_clinic_name, '\" deleted successfully');
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
        $connection = DB::connection('shafiqah');

        $procedures = [
            'sp_clinic_delete',
            'sp_clinic_update',
            'sp_clinic_read_all',
            'sp_clinic_read',
            'sp_clinic_create',
        ];

        foreach ($procedures as $procedure) {
            $connection->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }
};
