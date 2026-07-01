<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION fn_user_create(
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
    v_status VARCHAR;
    v_message VARCHAR;
BEGIN
    -- Call the TRUE PROCEDURE
    CALL sp_user_create_proc(
        p_name, p_email, p_password, p_phone_num, p_address, p_city, p_state,
        p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
        v_user_id, v_status, v_message
    );

    -- Return OUT parameters as table
    RETURN QUERY SELECT v_user_id, v_status, v_message;
END;
$$ LANGUAGE plpgsql;
SQL;
