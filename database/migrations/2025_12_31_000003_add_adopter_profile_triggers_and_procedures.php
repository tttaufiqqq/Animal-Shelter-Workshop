<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adopter Profile Management - Validation and audit logging
     */
    public function up(): void
    {
        // 1. Validate Adopter Profile Data
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION validate_adopter_profile()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Validate housing_type
                IF NEW.housing_type IS NOT NULL AND
                   NEW.housing_type NOT IN ('condo', 'landed', 'apartment', 'house') THEN
                    RAISE EXCEPTION 'Invalid housing_type: %. Must be one of: condo, landed, apartment, house', NEW.housing_type;
                END IF;

                -- Validate activity_level
                IF NEW.activity_level IS NOT NULL AND
                   NEW.activity_level NOT IN ('low', 'medium', 'high') THEN
                    RAISE EXCEPTION 'Invalid activity_level: %. Must be one of: low, medium, high', NEW.activity_level;
                END IF;

                -- Validate experience
                IF NEW.experience IS NOT NULL AND
                   NEW.experience NOT IN ('beginner', 'intermediate', 'expert') THEN
                    RAISE EXCEPTION 'Invalid experience: %. Must be one of: beginner, intermediate, expert', NEW.experience;
                END IF;

                -- Validate preferred_species
                IF NEW.preferred_species IS NOT NULL AND
                   NEW.preferred_species NOT IN ('cat', 'dog', 'both') THEN
                    RAISE EXCEPTION 'Invalid preferred_species: %. Must be one of: cat, dog, both', NEW.preferred_species;
                END IF;

                -- Validate preferred_size
                IF NEW.preferred_size IS NOT NULL AND
                   NEW.preferred_size NOT IN ('small', 'medium', 'large') THEN
                    RAISE EXCEPTION 'Invalid preferred_size: %. Must be one of: small, medium, large', NEW.preferred_size;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_validate_adopter_profile
                BEFORE INSERT OR UPDATE ON adopter_profile
                FOR EACH ROW
                EXECUTE FUNCTION validate_adopter_profile();
        ");

        // 2. Auto-Log Adopter Profile Changes
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION log_adopter_profile_changes()
            RETURNS TRIGGER AS $$
            DECLARE
                v_action VARCHAR(100);
                v_user_record RECORD;
            BEGIN
                -- Get user details
                SELECT id, name, email INTO v_user_record
                FROM users
                WHERE id = COALESCE(NEW.\"adopterID\", OLD.\"adopterID\")
                LIMIT 1;

                -- Determine action
                IF TG_OP = 'INSERT' THEN
                    v_action := 'adopter_profile_created';
                ELSIF TG_OP = 'UPDATE' THEN
                    v_action := 'adopter_profile_updated';
                ELSIF TG_OP = 'DELETE' THEN
                    v_action := 'adopter_profile_deleted';
                END IF;

                INSERT INTO audit_logs (
                    user_id, user_name, user_email,
                    category, action, entity_type, entity_id,
                    source_database, performed_at,
                    old_values, new_values, status
                ) VALUES (
                    v_user_record.id, v_user_record.name, v_user_record.email,
                    'adopter_management', v_action, 'AdopterProfile', COALESCE(NEW.id, OLD.id),
                    'taufiq', NOW(),
                    CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN to_jsonb(OLD) ELSE NULL END,
                    CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN to_jsonb(NEW) ELSE NULL END,
                    'success'
                );

                RETURN COALESCE(NEW, OLD);
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_log_adopter_profile_changes
                AFTER INSERT OR UPDATE OR DELETE ON adopter_profile
                FOR EACH ROW
                EXECUTE FUNCTION log_adopter_profile_changes();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_validate_adopter_profile ON adopter_profile');
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_log_adopter_profile_changes ON adopter_profile');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS validate_adopter_profile()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS log_adopter_profile_changes()');
    }
};
