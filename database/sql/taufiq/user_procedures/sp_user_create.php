<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION sp_user_create(
    p_name VARCHAR,
    p_email VARCHAR,
    p_password VARCHAR,
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
    o_user_id BIGINT,
    o_status VARCHAR,
    o_message VARCHAR
)
AS $$
DECLARE
    v_user_id BIGINT;
    v_exists BOOLEAN;
BEGIN
    -- Check if email already exists
    SELECT EXISTS(SELECT 1 FROM users WHERE email = p_email) INTO v_exists;

    IF v_exists THEN
        RETURN QUERY SELECT
            NULL::BIGINT,
            'error'::VARCHAR,
            'Email already exists'::VARCHAR;
        RETURN;
    END IF;

    -- Insert new user
    INSERT INTO users (name, email, password, "phoneNum", address, city, state, created_at, updated_at)
    VALUES (p_name, p_email, p_password, p_phone_num, p_address, p_city, p_state, NOW(), NOW())
    RETURNING id INTO v_user_id;

    -- Log to audit (manual log since this is pre-authentication)
    INSERT INTO audit_logs (
        user_id, user_name, user_email, user_role,
        category, action, entity_type, entity_id,
        source_database, performed_at, status,
        new_values
    ) VALUES (
        p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
        'user_management', 'user_created', 'User', v_user_id,
        'users', NOW(), 'success',
        jsonb_build_object(
            'name', p_name,
            'email', p_email,
            'phoneNum', p_phone_num,
            'city', p_city,
            'state', p_state
        )
    );

    RETURN QUERY SELECT
        v_user_id,
        'success'::VARCHAR,
        'User created successfully'::VARCHAR;
END;
$$ LANGUAGE plpgsql;
SQL;
