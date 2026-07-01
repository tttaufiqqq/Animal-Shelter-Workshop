<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION sp_user_update(
    p_user_id BIGINT,
    p_name VARCHAR,
    p_email VARCHAR,
    p_phone_num VARCHAR,
    p_address TEXT,
    p_city VARCHAR,
    p_state VARCHAR,
    p_audit_user_id BIGINT,
    p_audit_user_name VARCHAR,
    p_audit_user_email VARCHAR,
    p_audit_user_role VARCHAR
)
RETURNS TABLE(
    o_status VARCHAR,
    o_message VARCHAR
)
AS $$
DECLARE
    v_exists BOOLEAN;
    v_email_conflict BOOLEAN;
    v_old_values JSONB;
BEGIN
    -- Check if user exists
    SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

    IF NOT v_exists THEN
        RETURN QUERY SELECT
            'error'::VARCHAR,
            'User not found'::VARCHAR;
        RETURN;
    END IF;

    -- Check if email is taken by another user
    SELECT EXISTS(
        SELECT 1 FROM users
        WHERE email = p_email AND id != p_user_id
    ) INTO v_email_conflict;

    IF v_email_conflict THEN
        RETURN QUERY SELECT
            'error'::VARCHAR,
            'Email already exists'::VARCHAR;
        RETURN;
    END IF;

    -- Store old values for audit
    SELECT jsonb_build_object(
        'name', name,
        'email', email,
        'phoneNum', "phoneNum",
        'address', address,
        'city', city,
        'state', state
    ) INTO v_old_values
    FROM users WHERE id = p_user_id;

    -- Update user
    UPDATE users
    SET
        name = p_name,
        email = p_email,
        "phoneNum" = p_phone_num,
        address = p_address,
        city = p_city,
        state = p_state,
        updated_at = NOW()
    WHERE id = p_user_id;

    -- Note: Trigger trg_log_user_changes will automatically log this

    RETURN QUERY SELECT
        'success'::VARCHAR,
        'User updated successfully'::VARCHAR;
END;
$$ LANGUAGE plpgsql;
SQL;
