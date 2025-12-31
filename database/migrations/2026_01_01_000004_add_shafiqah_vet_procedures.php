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
        // sp_vet_create
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_vet_create
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_vet_create(
                IN p_name VARCHAR(255),
                IN p_email VARCHAR(255),
                IN p_contact_num VARCHAR(30),
                IN p_specialization VARCHAR(255),
                IN p_license_no VARCHAR(50),
                IN p_clinic_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_vet_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_email_exists INT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_vet_id = NULL;
                END;

                START TRANSACTION;

                -- Check email uniqueness (case-insensitive)
                SELECT COUNT(*) INTO v_email_exists
                FROM vet
                WHERE LOWER(email) = LOWER(p_email);

                IF v_email_exists > 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Email already exists';
                    SET o_vet_id = NULL;
                    ROLLBACK;
                ELSE
                    INSERT INTO vet (name, email, contactNum, specialization, license_no, clinicID, created_at, updated_at)
                    VALUES (p_name, p_email, p_contact_num, p_specialization, p_license_no, p_clinic_id, NOW(), NOW());

                    SET o_vet_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = CONCAT('Veterinarian \"', p_name, '\" created successfully');
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_vet_read_all
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_vet_read_all
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_vet_read_all()
            BEGIN
                SELECT
                    v.id,
                    v.name,
                    v.email,
                    v.contactNum,
                    v.specialization,
                    v.license_no,
                    v.clinicID,
                    c.name as clinic_name,
                    v.created_at,
                    v.updated_at
                FROM vet v
                LEFT JOIN clinic c ON v.clinicID = c.id
                ORDER BY v.name;
            END
        ");

        // ===========================
        // sp_vet_update
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_vet_update
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_vet_update(
                IN p_vet_id BIGINT,
                IN p_name VARCHAR(255),
                IN p_email VARCHAR(255),
                IN p_contact_num VARCHAR(30),
                IN p_specialization VARCHAR(255),
                IN p_license_no VARCHAR(50),
                IN p_clinic_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_email_exists INT;
                DECLARE v_current_email VARCHAR(255);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Get current email
                SELECT email INTO v_current_email FROM vet WHERE id = p_vet_id;

                IF v_current_email IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Veterinarian not found';
                    ROLLBACK;
                ELSE
                    -- Check email uniqueness (case-insensitive), excluding current vet
                    SELECT COUNT(*) INTO v_email_exists
                    FROM vet
                    WHERE LOWER(email) = LOWER(p_email) AND id != p_vet_id;

                    IF v_email_exists > 0 THEN
                        SET o_status = 'error';
                        SET o_message = 'Email already exists';
                        ROLLBACK;
                    ELSE
                        UPDATE vet
                        SET
                            name = p_name,
                            email = p_email,
                            contactNum = p_contact_num,
                            specialization = p_specialization,
                            license_no = p_license_no,
                            clinicID = p_clinic_id,
                            updated_at = NOW()
                        WHERE id = p_vet_id;

                        SET o_status = 'success';
                        SET o_message = CONCAT('Veterinarian \"', p_name, '\" updated successfully');
                        COMMIT;
                    END IF;
                END IF;
            END
        ");

        // ===========================
        // sp_vet_delete
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_vet_delete
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_vet_delete(
                IN p_vet_id BIGINT,
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE v_medical_count INT;
                DECLARE v_vaccination_count INT;
                DECLARE v_vet_name VARCHAR(255);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                END;

                START TRANSACTION;

                -- Check if vet exists
                SELECT name INTO v_vet_name FROM vet WHERE id = p_vet_id;

                IF v_vet_name IS NULL THEN
                    SET o_status = 'error';
                    SET o_message = 'Veterinarian not found';
                    ROLLBACK;
                ELSE
                    -- Check for associated records (referential integrity)
                    SELECT COUNT(*) INTO v_medical_count FROM medical WHERE vetID = p_vet_id;
                    SELECT COUNT(*) INTO v_vaccination_count FROM vaccination WHERE vetID = p_vet_id;

                    IF v_medical_count > 0 OR v_vaccination_count > 0 THEN
                        SET o_status = 'error';
                        SET o_message = CONCAT('Cannot delete veterinarian with associated medical records (', v_medical_count, ') or vaccination records (', v_vaccination_count, '). Please reassign these records first.');
                        ROLLBACK;
                    ELSE
                        DELETE FROM vet WHERE id = p_vet_id;

                        SET o_status = 'success';
                        SET o_message = CONCAT('Veterinarian \"', v_vet_name, '\" deleted successfully');
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
            'sp_vet_delete',
            'sp_vet_update',
            'sp_vet_read_all',
            'sp_vet_create',
        ];

        foreach ($procedures as $procedure) {
            $connection->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }
};
