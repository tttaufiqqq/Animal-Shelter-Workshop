<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Data Integrity & Consistency - Prevent invalid deletions, enforce unique constraints
     */
    public function up(): void
    {
        // 1. Prevent User Deletion with Active Relationships
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION prevent_user_deletion_with_active_data()
            RETURNS TRIGGER AS $$
            DECLARE
                profile_count INTEGER;
                role_count INTEGER;
            BEGIN
                -- Check for adopter profile
                SELECT COUNT(*) INTO profile_count
                FROM adopter_profile
                WHERE \"adopterID\" = OLD.id;

                -- Check for role assignments
                SELECT COUNT(*) INTO role_count
                FROM model_has_roles
                WHERE model_id = OLD.id AND model_type = 'App\\Models\\User';

                -- Block deletion if user has active relationships (optional - can be disabled)
                -- Commenting out to allow cascade delete instead
                -- IF profile_count > 0 OR role_count > 0 THEN
                --     RAISE EXCEPTION 'Cannot delete user with active relationships (profiles: %, roles: %)',
                --         profile_count, role_count;
                -- END IF;

                -- Log the deletion attempt instead
                INSERT INTO audit_logs (
                    user_id, user_name, user_email,
                    category, action, entity_type, entity_id,
                    source_database, performed_at,
                    old_values, metadata, status
                ) VALUES (
                    OLD.id, OLD.name, OLD.email,
                    'user_management', 'user_deletion_attempted', 'User', OLD.id,
                    'taufiq', NOW(),
                    to_jsonb(OLD),
                    jsonb_build_object(
                        'profile_count', profile_count,
                        'role_count', role_count
                    ),
                    'success'
                );

                RETURN OLD;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_prevent_user_deletion
                BEFORE DELETE ON users
                FOR EACH ROW
                EXECUTE FUNCTION prevent_user_deletion_with_active_data();
        ");

        // 2. Ensure Email Uniqueness (Case-Insensitive)
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION enforce_unique_email()
            RETURNS TRIGGER AS $$
            DECLARE
                existing_count INTEGER;
            BEGIN
                SELECT COUNT(*) INTO existing_count
                FROM users
                WHERE LOWER(email) = LOWER(NEW.email)
                  AND id != COALESCE(NEW.id, 0);

                IF existing_count > 0 THEN
                    RAISE EXCEPTION 'Email address already exists: %', NEW.email;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_enforce_unique_email
                BEFORE INSERT OR UPDATE ON users
                FOR EACH ROW
                EXECUTE FUNCTION enforce_unique_email();
        ");

        // 3. Normalize Email to Lowercase
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION normalize_email()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Automatically convert email to lowercase
                NEW.email := LOWER(NEW.email);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_normalize_email
                BEFORE INSERT OR UPDATE ON users
                FOR EACH ROW
                WHEN (NEW.email IS NOT NULL)
                EXECUTE FUNCTION normalize_email();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_prevent_user_deletion ON users');
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_enforce_unique_email ON users');
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_normalize_email ON users');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS prevent_user_deletion_with_active_data()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS enforce_unique_email()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS normalize_email()');
    }
};
