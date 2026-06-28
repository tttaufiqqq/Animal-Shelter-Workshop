<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection('booking');

        // ===========================
        // sp_visit_list_get_or_create
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_or_create');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_get_or_create(
                IN p_user_id BIGINT,
                OUT o_list_id BIGINT,
                OUT o_is_new TINYINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_list_id = NULL;
                    SET o_is_new = 0;
                END;

                SELECT id INTO o_list_id
                FROM visit_list
                WHERE userID = p_user_id
                LIMIT 1;

                IF o_list_id IS NULL THEN
                    START TRANSACTION;

                    INSERT INTO visit_list (userID, created_at, updated_at)
                    VALUES (p_user_id, NOW(), NOW());

                    SET o_list_id = LAST_INSERT_ID();
                    SET o_is_new = 1;
                    SET o_message = 'Visit list created successfully';

                    COMMIT;
                ELSE
                    SET o_is_new = 0;
                    SET o_message = 'Visit list already exists';
                END IF;

                SET o_status = 'success';
            END
        ");

        // ===========================
        // sp_visit_list_add_animal
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_add_animal');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_add_animal(
                IN p_list_id BIGINT,
                IN p_animal_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_exists INT DEFAULT 0;
                DECLARE v_duplicate INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                END;

                SELECT COUNT(*) INTO v_exists FROM visit_list WHERE id = p_list_id;
                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Visit list not found';
                    LEAVE proc_exit;
                END IF;

                SELECT COUNT(*) INTO v_duplicate
                FROM visit_list_animal
                WHERE listID = p_list_id AND animalID = p_animal_id;

                IF v_duplicate > 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'This animal is already in your visit list';
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                INSERT INTO visit_list_animal (listID, animalID, created_at, updated_at)
                VALUES (p_list_id, p_animal_id, NOW(), NOW());

                UPDATE visit_list SET updated_at = NOW() WHERE id = p_list_id;

                SET o_status = 'success';
                SET o_message = 'Animal added to visit list successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_visit_list_remove_animal
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_remove_animal');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_remove_animal(
                IN p_list_id BIGINT,
                IN p_animal_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_exists INT DEFAULT 0;
                DECLARE v_animal_exists INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                END;

                SELECT COUNT(*) INTO v_exists FROM visit_list WHERE id = p_list_id;
                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Visit list not found';
                    LEAVE proc_exit;
                END IF;

                SELECT COUNT(*) INTO v_animal_exists
                FROM visit_list_animal
                WHERE listID = p_list_id AND animalID = p_animal_id;

                IF v_animal_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Animal not found in visit list';
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                DELETE FROM visit_list_animal WHERE listID = p_list_id AND animalID = p_animal_id;

                UPDATE visit_list SET updated_at = NOW() WHERE id = p_list_id;

                SET o_status = 'success';
                SET o_message = 'Animal removed from visit list successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_visit_list_get_animals
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_animals');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_get_animals(
                IN p_list_id BIGINT
            )
            BEGIN
                SELECT animalID, created_at, updated_at
                FROM visit_list_animal
                WHERE listID = p_list_id
                ORDER BY created_at DESC;
            END
        ");

        // ===========================
        // sp_visit_list_delete
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_delete');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_delete(
                IN p_list_id BIGINT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_exists INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                END;

                SELECT COUNT(*) INTO v_exists FROM visit_list WHERE id = p_list_id;
                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Visit list not found';
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                DELETE FROM visit_list_animal WHERE listID = p_list_id;
                DELETE FROM visit_list WHERE id = p_list_id;

                SET o_status = 'success';
                SET o_message = 'Visit list deleted successfully';

                COMMIT;
            END
        ");

        // ===========================
        // sp_visit_list_clear_animals
        // ===========================
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_clear_animals');

        $connection->unprepared("
            CREATE PROCEDURE sp_visit_list_clear_animals(
                IN p_list_id BIGINT,
                IN p_animal_ids TEXT,
                OUT o_removed_count INT,
                OUT o_status VARCHAR(20),
                OUT o_message TEXT
            )
            proc_exit: BEGIN
                DECLARE v_exists INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET o_status = 'error';
                    SET o_message = 'Database error occurred';
                    SET o_removed_count = 0;
                END;

                SELECT COUNT(*) INTO v_exists FROM visit_list WHERE id = p_list_id;
                IF v_exists = 0 THEN
                    SET o_status = 'error';
                    SET o_message = 'Visit list not found';
                    SET o_removed_count = 0;
                    LEAVE proc_exit;
                END IF;

                START TRANSACTION;

                DELETE FROM visit_list_animal
                WHERE listID = p_list_id
                  AND FIND_IN_SET(animalID, p_animal_ids) > 0;

                SET o_removed_count = ROW_COUNT();

                UPDATE visit_list SET updated_at = NOW() WHERE id = p_list_id;

                SET o_status = 'success';
                SET o_message = CONCAT(o_removed_count, ' animal(s) removed from visit list');

                COMMIT;
            END
        ");
    }

    public function down(): void
    {
        $connection = DB::connection('booking');

        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_or_create');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_add_animal');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_remove_animal');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_get_animals');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_delete');
        $connection->unprepared('DROP PROCEDURE IF EXISTS sp_visit_list_clear_animals');
    }
};
