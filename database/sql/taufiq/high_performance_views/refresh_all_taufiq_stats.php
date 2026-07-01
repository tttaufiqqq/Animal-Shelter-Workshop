<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION refresh_all_taufiq_stats()
RETURNS TEXT
AS $$
BEGIN
    REFRESH MATERIALIZED VIEW v_user_account_stats;
    REFRESH MATERIALIZED VIEW v_adopter_profile_stats;
    RETURN 'All materialized views refreshed at ' || NOW()::TEXT;
END;
$$ LANGUAGE plpgsql;
SQL;
