<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION fn_user_delete(
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
    v_user_name VARCHAR;
    v_status VARCHAR;
    v_message VARCHAR;
BEGIN
    CALL sp_user_delete_proc(
        p_user_id,
        p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
        v_user_name, v_status, v_message
    );
    RETURN QUERY SELECT v_user_name, v_status, v_message;
END;
$$ LANGUAGE plpgsql;
SQL;
