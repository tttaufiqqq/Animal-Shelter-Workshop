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
        // sp_medical_create
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_medical_create
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_medical_create(
                IN p_animal_id BIGINT,
                IN p_treatment_type VARCHAR(100),
                IN p_diagnosis TEXT,
                IN p_action TEXT,
                IN p_remarks TEXT,
                IN p_vet_id BIGINT,
                IN p_costs DECIMAL(10,2),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_medical_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_medical_id = NULL;
                END;

                START TRANSACTION;

                -- Check if animal exists
                IF NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found';
                    SET o_medical_id = NULL;
                    ROLLBACK;
                -- Check if vet exists
                ELSEIF NOT EXISTS (SELECT 1 FROM vet WHERE id = p_vet_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Veterinarian not found';
                    SET o_medical_id = NULL;
                    ROLLBACK;
                ELSE
                    INSERT INTO medical (
                        animalID, treatment_type, diagnosis, action, remarks, vetID, costs,
                        created_at, updated_at
                    )
                    VALUES (
                        p_animal_id, p_treatment_type, p_diagnosis, p_action, p_remarks, p_vet_id, p_costs,
                        NOW(), NOW()
                    );

                    SET o_medical_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Medical record created successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_medical_read_by_animal
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_medical_read_by_animal
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_medical_read_by_animal(
                IN p_animal_id BIGINT
            )
            BEGIN
                SELECT
                    m.id,
                    m.treatment_type,
                    m.diagnosis,
                    m.action,
                    m.remarks,
                    m.costs,
                    m.animalID,
                    m.vetID,
                    v.name as vet_name,
                    v.specialization as vet_specialization,
                    m.created_at,
                    m.updated_at
                FROM medical m
                LEFT JOIN vet v ON m.vetID = v.id
                WHERE m.animalID = p_animal_id
                ORDER BY m.created_at DESC;
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
            'sp_medical_read_by_animal',
            'sp_medical_create',
        ];

        foreach ($procedures as $procedure) {
            $connection->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }
};
