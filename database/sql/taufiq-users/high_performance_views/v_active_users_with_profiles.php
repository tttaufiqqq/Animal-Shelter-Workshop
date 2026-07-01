<?php

return <<<'SQL'
CREATE OR REPLACE VIEW v_active_users_with_profiles AS
SELECT
    u.id,
    u.name,
    u.email,
    u."phoneNum",
    u.city,
    u.state,
    u.created_at,

    ap.id AS adopter_profile_id,
    ap.housing_type,
    ap.has_children,
    ap.has_other_pets,
    ap.activity_level,
    ap.experience,
    ap.preferred_species,
    ap.preferred_size,
    ap.created_at AS profile_created_at,

    -- Adopter Readiness Score (0-100)
    (
        CASE WHEN ap.housing_type IS NOT NULL THEN 25 ELSE 0 END +
        CASE WHEN ap.activity_level IS NOT NULL THEN 25 ELSE 0 END +
        CASE WHEN ap.experience IS NOT NULL THEN 25 ELSE 0 END +
        CASE WHEN ap.preferred_species IS NOT NULL THEN 25 ELSE 0 END
    ) AS readiness_score,

    -- Profile Completeness
    CASE
        WHEN ap.housing_type IS NOT NULL
         AND ap.activity_level IS NOT NULL
         AND ap.experience IS NOT NULL
         AND ap.preferred_species IS NOT NULL
        THEN TRUE
        ELSE FALSE
    END AS is_profile_complete

FROM users u
INNER JOIN adopter_profile ap ON ap."adopterID" = u.id
WHERE u.account_status = 'active'
ORDER BY ap.created_at DESC;
SQL;
