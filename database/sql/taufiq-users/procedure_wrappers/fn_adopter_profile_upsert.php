<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION fn_adopter_profile_upsert(
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
AS $$
DECLARE
    v_profile_id BIGINT;
    v_status VARCHAR;
    v_message VARCHAR;
BEGIN
    CALL sp_adopter_profile_upsert_proc(
        p_adopter_id, p_housing_type, p_has_children, p_has_other_pets,
        p_activity_level, p_experience, p_preferred_species, p_preferred_size,
        p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
        v_profile_id, v_status, v_message
    );
    RETURN QUERY SELECT v_profile_id, v_status, v_message;
END;
$$ LANGUAGE plpgsql;
SQL;
