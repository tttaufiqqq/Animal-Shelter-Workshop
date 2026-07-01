<?php

return <<<'SQL'
CREATE OR REPLACE VIEW v_user_full_profile AS
SELECT
    u.id,
    u.name,
    u.email,
    u."phoneNum",
    u.address,
    u.city,
    u.state,
    u.account_status,
    u.suspended_at,
    u.suspended_by,
    u.suspension_reason,
    u.locked_until,
    u.lock_reason,
    u.failed_login_attempts,
    u.last_failed_login_at,
    u.require_password_reset,
    u.created_at AS user_created_at,
    u.updated_at AS user_updated_at,

    -- Adopter Profile (NULL if not exists)
    ap.id AS adopter_profile_id,
    ap.housing_type,
    ap.has_children,
    ap.has_other_pets,
    ap.activity_level,
    ap.experience,
    ap.preferred_species,
    ap.preferred_size,
    ap.created_at AS adopter_profile_created_at,
    ap.updated_at AS adopter_profile_updated_at,

    -- Role Information (aggregated)
    COALESCE(
        (SELECT STRING_AGG(r.name, ', ' ORDER BY r.name)
         FROM model_has_roles mhr
         JOIN roles r ON r.id = mhr.role_id
         WHERE mhr.model_id = u.id
           AND mhr.model_type = 'App\Models\User'),
        'User'
    ) AS roles,

    -- Computed Status Flags
    CASE
        WHEN u.account_status = 'suspended' THEN TRUE
        ELSE FALSE
    END AS is_suspended,

    CASE
        WHEN u.account_status = 'locked' AND u.locked_until > NOW() THEN TRUE
        ELSE FALSE
    END AS is_locked,

    CASE
        WHEN u.require_password_reset = TRUE THEN TRUE
        ELSE FALSE
    END AS needs_password_reset,

    CASE
        WHEN u.failed_login_attempts >= 3 THEN TRUE
        ELSE FALSE
    END AS is_high_risk

FROM users u
LEFT JOIN adopter_profile ap ON ap."adopterID" = u.id;
SQL;
