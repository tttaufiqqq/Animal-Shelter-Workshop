<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION fn_user_lock(
    p_user_id BIGINT,
    p_duration_minutes INTEGER,
    p_reason TEXT,
    p_audit_user_id BIGINT,
    p_audit_user_name VARCHAR,
    p_audit_user_email VARCHAR,
    p_audit_user_role VARCHAR
)
RETURNS TABLE(
    o_locked_until TIMESTAMP,
    o_status VARCHAR,
    o_message VARCHAR
)
AS $$
DECLARE
    v_locked_until TIMESTAMP;
    v_status VARCHAR;
    v_message VARCHAR;
BEGIN
    CALL sp_user_lock_proc(
        p_user_id, p_duration_minutes, p_reason,
        p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
        v_locked_until, v_status, v_message
    );
    RETURN QUERY SELECT v_locked_until, v_status, v_message;
END;
$$ LANGUAGE plpgsql;
SQL;
