<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDatabaseHealth
{
    /**
     * Databases to check for existence.
     * IMPORTANT: ONLY check the 5 main distributed databases.
     * NEVER check the 'backup' database - it's independent and should always be available.
     *
     * @var array
     */
    protected $connections = ['eilya', 'atiqah', 'shafiqah', 'danish', 'taufiq'];

    /**
     * Databases to EXCLUDE from checking (backup system database).
     *
     * @var array
     */
    protected $excludedConnections = ['backup'];

    /**
     * Routes that should bypass database health check.
     *
     * @var array
     */
    protected $excludedRoutes = [
        'backup-login',
        'backup-login-submit',
        'backup-dashboard',
        'backup-logout',
        'backup-all-databases',
    ];

    /**
     * Cache duration in seconds (5 minutes).
     *
     * @var int
     */
    protected $cacheDuration = 300;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check if already on backup routes
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Check if any databases don't exist (with caching to prevent repeated checks)
        $nonExistentDatabases = $this->getCachedDatabaseHealth();

        if (!empty($nonExistentDatabases)) {
            // Store unavailable databases in session
            session(['unavailable_databases' => $nonExistentDatabases]);

            // Redirect to backup login page
            return redirect()->route('backup-login')
                ->with('error', 'Some databases are unavailable. Please login to the backup system.');
        }

        return $next($request);
    }

    /**
     * Get cached database health check results.
     *
     * @return array
     */
    protected function getCachedDatabaseHealth(): array
    {
        return Cache::remember('database_health_check', $this->cacheDuration, function () {
            return $this->checkDatabasesExistence();
        });
    }

    /**
     * Check if the current route is excluded from database health check.
     *
     * @param  Request  $request
     * @return bool
     */
    protected function isExcludedRoute(Request $request): bool
    {
        $currentRoute = $request->route()?->getName();

        return in_array($currentRoute, $this->excludedRoutes) ||
               str_starts_with($request->path(), 'backup-');
    }

    /**
     * Check if databases exist (not just connectivity, but actual database existence).
     *
     * @return array Array of non-existent database connection names
     */
    protected function checkDatabasesExistence(): array
    {
        $nonExistentDatabases = [];

        foreach ($this->connections as $connection) {
            // Safeguard: Skip backup database (should never happen, but double-check)
            if (in_array($connection, $this->excludedConnections)) {
                \Log::debug("Skipping '{$connection}' database check (excluded)");
                continue;
            }

            try {
                // Get the expected database name from config
                $expectedDatabase = config("database.connections.{$connection}.database");

                // Try to select the current database name
                $result = DB::connection($connection)->select('SELECT DATABASE() as db_name');

                // Check if the result is null (database doesn't exist) or empty
                if (empty($result) || is_null($result[0]->db_name)) {
                    \Log::warning("Database check: '{$connection}' - No database selected");
                    $nonExistentDatabases[] = $connection;
                } elseif ($result[0]->db_name !== $expectedDatabase) {
                    // Connected but to wrong database (shouldn't happen, but check anyway)
                    \Log::warning("Database check: '{$connection}' - Connected to '{$result[0]->db_name}' but expected '{$expectedDatabase}'");
                    $nonExistentDatabases[] = $connection;
                } else {
                    // Database exists and is correct
                    \Log::debug("Database check: '{$connection}' - OK (database: {$result[0]->db_name})");
                }
            } catch (\PDOException $e) {
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();

                // ONLY treat as non-existent if:
                // 1. MySQL: Error 1049 (Unknown database)
                // 2. PostgreSQL: SQLSTATE 3D000 (invalid catalog name = database doesn't exist)
                // 3. SQL Server: Error message contains "Cannot open database"

                // MySQL: SQLSTATE[HY000] [1049] Unknown database
                $isMySQLDatabaseNotFound = $errorCode === '1049' ||
                    str_contains($errorMessage, '[1049]') ||
                    str_contains($errorMessage, 'Unknown database');

                // PostgreSQL: SQLSTATE[3D000] or "database ... does not exist"
                $isPostgreSQLDatabaseNotFound = $errorCode === '3D000' ||
                    str_contains($errorMessage, 'database') && str_contains($errorMessage, 'does not exist');

                // SQL Server: "Cannot open database"
                $isSQLServerDatabaseNotFound = str_contains($errorMessage, 'Cannot open database');

                // IGNORE connection failures:
                // - SQLSTATE[HY000] [2002] Connection refused
                // - SQLSTATE[HY000] [2003] Can't connect to MySQL server
                // - SQLSTATE[08006] Connection failure (PostgreSQL)
                // - SQLSTATE[08001] Unable to connect
                $isConnectionFailure = str_contains($errorMessage, 'Connection refused') ||
                    str_contains($errorMessage, 'Connection timed out') ||
                    str_contains($errorMessage, 'Can\'t connect to') ||
                    str_contains($errorMessage, 'Unable to connect') ||
                    str_contains($errorMessage, '[2002]') ||
                    str_contains($errorMessage, '[2003]') ||
                    $errorCode === '08006' ||
                    $errorCode === '08001' ||
                    $errorCode === '2002' ||
                    $errorCode === '2003';

                // Only add to non-existent list if it's a "database doesn't exist" error
                // NOT a connection failure
                if (!$isConnectionFailure &&
                    ($isMySQLDatabaseNotFound || $isPostgreSQLDatabaseNotFound || $isSQLServerDatabaseNotFound)) {
                    $nonExistentDatabases[] = $connection;
                    \Log::warning("Database '{$connection}' does not exist (connected to server but database is missing)");
                } elseif ($isConnectionFailure) {
                    // Connection failure - SSH tunnel likely not running, ignore
                    \Log::debug("Connection failure for '{$connection}' - SSH tunnel may not be running (this is normal)");
                }
            } catch (\Exception $e) {
                // Generic error handling - log but don't block
                \Log::debug("Database health check skipped for {$connection}: " . $e->getMessage());
            }
        }

        // Log summary
        if (!empty($nonExistentDatabases)) {
            \Log::warning("Database Health Check Summary: " . count($nonExistentDatabases) . " databases missing: " . implode(', ', $nonExistentDatabases));
        } else {
            \Log::debug("Database Health Check Summary: All databases OK (checked: " . implode(', ', $this->connections) . ")");
        }

        return $nonExistentDatabases;
    }
}
