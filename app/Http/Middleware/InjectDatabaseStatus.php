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
        $dbStatus = $this->checker->checkAll();
        $connected = array_filter($dbStatus, fn($db) => $db['connected']);
        $disconnected = array_filter($dbStatus, fn($db) => !$db['connected']);

        View::share('dbConnectionStatus', $dbStatus);
        View::share('dbConnected', $connected);
        View::share('dbDisconnected', $disconnected);
        View::share('allDatabasesOnline', count($disconnected) === 0);

        return $next($request);
    }
}
