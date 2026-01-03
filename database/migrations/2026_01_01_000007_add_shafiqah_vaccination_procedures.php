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
        // sp_vaccination_create
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_vaccination_create
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_vaccination_create(
                IN p_animal_id BIGINT,
                IN p_name VARCHAR(255),
                IN p_type VARCHAR(255),
                IN p_next_due_date DATE,
                IN p_remarks TEXT,
                IN p_vet_id BIGINT,
                IN p_costs DECIMAL(10,2),
                IN p_user_id BIGINT,
                IN p_user_name VARCHAR(255),
                IN p_user_email VARCHAR(255),
                OUT o_vaccination_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    GET DIAGNOSTICS CONDITION 1 o_message = MESSAGE_TEXT;
                    SET o_status = 'error';
                    SET o_vaccination_id = NULL;
                END;

                START TRANSACTION;

                -- Check if animal exists
                IF NOT EXISTS (SELECT 1 FROM animal WHERE id = p_animal_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found';
                    SET o_vaccination_id = NULL;
                    ROLLBACK;
                -- Check if vet exists
                ELSEIF NOT EXISTS (SELECT 1 FROM vet WHERE id = p_vet_id) THEN
                    SET o_status = 'error';
                    SET o_message = 'Veterinarian not found';
                    SET o_vaccination_id = NULL;
                    ROLLBACK;
                -- Validate next_due_date is in the future
                ELSEIF p_next_due_date IS NOT NULL AND p_next_due_date <= CURDATE() THEN
                    SET o_status = 'error';
                    SET o_message = 'Next due date must be in the future';
                    SET o_vaccination_id = NULL;
                    ROLLBACK;
                ELSE
                    INSERT INTO vaccination (
                        animalID, name, type, next_due_date, remarks, vetID, costs,
                        created_at, updated_at
                    )
                    VALUES (
                        p_animal_id, p_name, p_type, p_next_due_date, p_remarks, p_vet_id, p_costs,
                        NOW(), NOW()
                    );

                    SET o_vaccination_id = LAST_INSERT_ID();
                    SET o_status = 'success';
                    SET o_message = 'Vaccination record created successfully';
                    COMMIT;
                END IF;
            END
        ");

        // ===========================
        // sp_vaccination_read_by_animal
        // ===========================
        $connection->unprepared("
            DROP PROCEDURE IF EXISTS sp_vaccination_read_by_animal
        ");

        $connection->unprepared("
            CREATE PROCEDURE sp_vaccination_read_by_animal(
                IN p_animal_id BIGINT
            )
            BEGIN
                SELECT
                    v.id,
                    v.name,
                    v.type,
                    v.next_due_date,
                    v.remarks,
                    v.costs,
                    v.animalID,
                    v.vetID,
                    vt.name as vet_name,
                    vt.specialization as vet_specialization,
                    v.created_at,
                    v.updated_at
                FROM vaccination v
                LEFT JOIN vet vt ON v.vetID = vt.id
                WHERE v.animalID = p_animal_id
                ORDER BY v.created_at DESC;
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
            'sp_vaccination_read_by_animal',
            'sp_vaccination_create',
        ];

        foreach ($procedures as $procedure) {
            $connection->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }
};
