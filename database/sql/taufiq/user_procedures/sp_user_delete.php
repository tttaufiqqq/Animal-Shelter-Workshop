<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION sp_user_delete(
    p_user_id BIGINT,
    p_audit_user_id BIGINT,
    p_audit_user_name VARCHAR,
    p_audit_user_email VARCHAR,
    p_audit_user_role VARCHAR
)
RETURNS TABLE(
    o_user_name VARCHAR,
    o_status VARCHAR,
    o_message VARCHAR
)
AS $$
DECLARE
    v_exists BOOLEAN;
    v_user_name VARCHAR;
BEGIN
    -- Check if user exists
    SELECT name INTO v_user_name FROM users WHERE id = p_user_id;

    IF v_user_name IS NULL THEN
        RETURN QUERY SELECT
            NULL::VARCHAR,
            'error'::VARCHAR,
            'User not found'::VARCHAR;
        RETURN;
    END IF;

    -- Delete user (cascade will delete adopter_profile)
    DELETE FROM users WHERE id = p_user_id;

    -- Note: Trigger trg_log_user_changes will automatically log this

    RETURN QUERY SELECT
        v_user_name,
        'success'::VARCHAR,
        'User deleted successfully'::VARCHAR;
END;
$$ LANGUAGE plpgsql;
SQL;
