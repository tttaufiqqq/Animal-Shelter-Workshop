<?php

return <<<'SQL'
CREATE MATERIALIZED VIEW v_adopter_profile_stats AS
SELECT
    -- Total Adopter Profiles
    COUNT(*)::INTEGER AS total_adopter_profiles,

    -- Breakdown by Housing Type
    COUNT(*) FILTER (WHERE housing_type = 'house')::INTEGER AS house_dwellers,
    COUNT(*) FILTER (WHERE housing_type = 'apartment')::INTEGER AS apartment_dwellers,
    COUNT(*) FILTER (WHERE housing_type = 'condo')::INTEGER AS condo_dwellers,

    -- Breakdown by Children
    COUNT(*) FILTER (WHERE has_children = TRUE)::INTEGER AS profiles_with_children,
    COUNT(*) FILTER (WHERE has_children = FALSE)::INTEGER AS profiles_without_children,

    -- Breakdown by Pets
    COUNT(*) FILTER (WHERE has_other_pets = TRUE)::INTEGER AS profiles_with_other_pets,
    COUNT(*) FILTER (WHERE has_other_pets = FALSE)::INTEGER AS profiles_without_other_pets,

    -- Breakdown by Activity Level
    COUNT(*) FILTER (WHERE activity_level = 'low')::INTEGER AS low_activity,
    COUNT(*) FILTER (WHERE activity_level = 'moderate')::INTEGER AS moderate_activity,
    COUNT(*) FILTER (WHERE activity_level = 'high')::INTEGER AS high_activity,

    -- Breakdown by Experience
    COUNT(*) FILTER (WHERE experience = 'none')::INTEGER AS no_experience,
    COUNT(*) FILTER (WHERE experience = 'beginner')::INTEGER AS beginner_experience,
    COUNT(*) FILTER (WHERE experience = 'intermediate')::INTEGER AS intermediate_experience,
    COUNT(*) FILTER (WHERE experience = 'expert')::INTEGER AS expert_experience,

    -- Breakdown by Preferred Species
    COUNT(*) FILTER (WHERE preferred_species = 'cat')::INTEGER AS prefer_cats,
    COUNT(*) FILTER (WHERE preferred_species = 'dog')::INTEGER AS prefer_dogs,
    COUNT(*) FILTER (WHERE preferred_species = 'both')::INTEGER AS prefer_both,

    -- Breakdown by Preferred Size
    COUNT(*) FILTER (WHERE preferred_size = 'small')::INTEGER AS prefer_small,
    COUNT(*) FILTER (WHERE preferred_size = 'medium')::INTEGER AS prefer_medium,
    COUNT(*) FILTER (WHERE preferred_size = 'large')::INTEGER AS prefer_large,

    -- New Profiles This Month
    COUNT(*) FILTER (
        WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
    )::INTEGER AS new_profiles_this_month,

    -- Completion Rate (profiles vs total users)
    ROUND(
        COUNT(*)::NUMERIC / NULLIF(
            (SELECT COUNT(*) FROM users),
            0
        ) * 100,
        2
    ) AS profile_completion_rate,

    -- Last Updated Timestamp
    NOW() AS stats_generated_at

FROM adopter_profile;

CREATE UNIQUE INDEX idx_adopter_profile_stats_singleton
ON v_adopter_profile_stats ((1));
SQL;
