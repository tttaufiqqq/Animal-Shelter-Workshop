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
        // ==========================================
        // ADOPTER PROFILE UPSERT PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_adopter_profile_upsert(
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
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_profile_id BIGINT,
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_profile_id BIGINT;
                v_user_exists BOOLEAN;
                v_is_update BOOLEAN DEFAULT FALSE;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_adopter_id) INTO v_user_exists;

                IF NOT v_user_exists THEN
                    RETURN QUERY SELECT
                        NULL::BIGINT,
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Check if profile already exists
                SELECT id INTO v_profile_id FROM adopter_profile WHERE \"adopterID\" = p_adopter_id;

                IF v_profile_id IS NOT NULL THEN
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
                RETURNING id INTO v_profile_id;

                -- Note: Trigger trg_log_adopter_profile_changes will automatically log this

                RETURN QUERY SELECT
                    v_profile_id,
                    'success'::VARCHAR,
                    CASE
                        WHEN v_is_update THEN 'Adopter profile updated successfully'
                        ELSE 'Adopter profile created successfully'
                    END::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // ADOPTER PROFILE READ PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_adopter_profile_read(p_adopter_id BIGINT)
            RETURNS TABLE(
                id BIGINT,
                \"adopterID\" BIGINT,
                housing_type VARCHAR,
                has_children BOOLEAN,
                has_other_pets BOOLEAN,
                activity_level VARCHAR,
                experience VARCHAR,
                preferred_species VARCHAR,
                preferred_size VARCHAR,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
            AS \$\$
            BEGIN
                RETURN QUERY
                SELECT
                    ap.id, ap.\"adopterID\", ap.housing_type, ap.has_children, ap.has_other_pets,
                    ap.activity_level, ap.experience, ap.preferred_species, ap.preferred_size,
                    ap.created_at, ap.updated_at
                FROM adopter_profile ap
                WHERE ap.\"adopterID\" = p_adopter_id;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // ADOPTER PROFILE DELETE PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_adopter_profile_delete(
                p_adopter_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
            BEGIN
                -- Check if profile exists
                SELECT EXISTS(
                    SELECT 1 FROM adopter_profile WHERE \"adopterID\" = p_adopter_id
                ) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'Adopter profile not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Delete profile
                DELETE FROM adopter_profile WHERE \"adopterID\" = p_adopter_id;

                -- Note: Trigger trg_log_adopter_profile_changes will automatically log this

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    'Adopter profile deleted successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_adopter_profile_upsert');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_adopter_profile_read');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_adopter_profile_delete');
    }
};
