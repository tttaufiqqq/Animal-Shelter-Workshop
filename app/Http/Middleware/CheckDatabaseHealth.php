<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDatabaseHealth
{
    /**
     * Databases to check for existence.
     *
     * @var array
     */
    protected $connections = ['eilya', 'atiqah', 'shafiqah', 'danish', 'taufiq'];

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

        // Check if any databases don't exist
        $nonExistentDatabases = $this->checkDatabasesExistence();

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
            try {
                // Try to select the database name
                $result = DB::connection($connection)->select('SELECT DATABASE() as db_name');

                // Check if the result is null (database doesn't exist) or empty
                if (empty($result) || is_null($result[0]->db_name)) {
                    $nonExistentDatabases[] = $connection;
                }
            } catch (\PDOException $e) {
                // Check if error is specifically about database not existing
                if (str_contains($e->getMessage(), 'Unknown database') ||
                    str_contains($e->getMessage(), 'does not exist') ||
                    str_contains($e->getMessage(), 'Cannot open database')) {
                    $nonExistentDatabases[] = $connection;
                }
                // For connection errors (timeout, etc.), we don't treat it as non-existent
                // Those are handled by the existing DatabaseErrorHandler trait
            } catch (\Exception $e) {
                // Generic error handling - log but don't block
                \Log::warning("Database health check error for {$connection}: " . $e->getMessage());
            }
        }

        return $nonExistentDatabases;
    }
}
