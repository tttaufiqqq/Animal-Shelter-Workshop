<?php

namespace App\Http\Middleware;

use App\Services\DatabaseConnectionChecker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InjectDatabaseStatus
{
    /**
     * Database connection checker service
     *
     * @var DatabaseConnectionChecker
     */
    protected $checker;

    /**
     * Create a new middleware instance.
     */
    public function __construct(DatabaseConnectionChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check connections once per session to speed up page loads
        // Use session storage with 30-minute expiry for much faster page loads
        $sessionKey = 'db_connection_status_checked';
        $expiryKey = 'db_connection_status_expiry';

        $shouldCheck = !session()->has($sessionKey)
                    || session($expiryKey, 0) < time()
                    || $request->query('refresh_db_status') === '1';

        if ($shouldCheck) {
            // Check database connections with cache
            $dbStatus = $this->checker->checkAll(true); // Use cache if available
            $connected = array_filter($dbStatus, fn($db) => $db['connected']);
            $disconnected = array_filter($dbStatus, fn($db) => !$db['connected']);

            // Store in session for 30 minutes (longer cache = faster pages)
            session([
                'db_connection_status' => $dbStatus,
                'db_connected' => $connected,
                'db_disconnected' => $disconnected,
                $sessionKey => true,
                $expiryKey => time() + 1800, // 30 minutes instead of 5
            ]);
        }

        // Get from session
        $dbStatus = session('db_connection_status', []);
        $connected = session('db_connected', []);
        $disconnected = session('db_disconnected', []);

        // Share with all views
        View::share('dbConnectionStatus', $dbStatus);
        View::share('dbConnected', $connected);
        View::share('dbDisconnected', $disconnected);
        View::share('allDatabasesOnline', count($disconnected) === 0);

        return $next($request);
    }
}
