<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates TRUE PostgreSQL PROCEDURES (not functions)
     * for AdopterProfile operations using CALL syntax with OUT parameters
     */
    public function up(): void
    {
        // ==========================================
        // ADOPTER PROFILE UPSERT PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_adopter_profile_upsert_proc(
                p_adopter_id BIGINT,
                p_housing_type VARCHAR,
                p_has_children BOOLEAN,
                p_has_other_pets BOOLEAN,
                p_activity_level VARCHAR,
                p_experience VARCHAR,
                p_preferred_species VARCHAR,
                p_preferred_size VARCHAR,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_profile_id BIGINT,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_user_exists BOOLEAN;
                v_is_update BOOLEAN DEFAULT FALSE;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_adopter_id) INTO v_user_exists;

                IF NOT v_user_exists THEN
                    o_profile_id := NULL;
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Check if profile already exists
                SELECT id INTO o_profile_id FROM adopter_profile WHERE \"adopterID\" = p_adopter_id;

                IF o_profile_id IS NOT NULL THEN
                    v_is_update := TRUE;
                END IF;

                -- Insert or update profile
                INSERT INTO adopter_profile (
                    \"adopterID\", housing_type, has_children, has_other_pets,
                    activity_level, experience, preferred_species, preferred_size,
                    created_at, updated_at
                )
                VALUES (
                    p_adopter_id, p_housing_type, p_has_children, p_has_other_pets,
                    p_activity_level, p_experience, p_preferred_species, p_preferred_size,
                    NOW(), NOW()
                )
                ON CONFLICT (\"adopterID\")
                DO UPDATE SET
                    housing_type = EXCLUDED.housing_type,
                    has_children = EXCLUDED.has_children,
                    has_other_pets = EXCLUDED.has_other_pets,
                    activity_level = EXCLUDED.activity_level,
                    experience = EXCLUDED.experience,
                    preferred_species = EXCLUDED.preferred_species,
                    preferred_size = EXCLUDED.preferred_size,
                    updated_at = NOW()
                RETURNING id INTO o_profile_id;

                -- Note: Trigger trg_log_adopter_profile_changes will automatically log this

                o_status := 'success';
                o_message := CASE
                    WHEN v_is_update THEN 'Adopter profile updated successfully'
                    ELSE 'Adopter profile created successfully'
                END;
            END;
            \$\$;
        ");

        // ==========================================
        // ADOPTER PROFILE DELETE PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_adopter_profile_delete_proc(
                p_adopter_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
            BEGIN
                -- Check if profile exists
                SELECT EXISTS(
                    SELECT 1 FROM adopter_profile WHERE \"adopterID\" = p_adopter_id
                ) INTO v_exists;

                IF NOT v_exists THEN
                    o_status := 'error';
                    o_message := 'Adopter profile not found';
                    RETURN;
                END IF;

                -- Delete profile
                DELETE FROM adopter_profile WHERE \"adopterID\" = p_adopter_id;

                -- Note: Trigger trg_log_adopter_profile_changes will automatically log this

                o_status := 'success';
                o_message := 'Adopter profile deleted successfully';
            END;
            \$\$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_adopter_profile_upsert_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_adopter_profile_delete_proc');
    }
};
