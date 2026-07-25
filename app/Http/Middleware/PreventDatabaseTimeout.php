<?php

namespace App\Http\Middleware;

use App\Services\DatabaseConnectionChecker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreventDatabaseTimeout
{
    /**
     * POST routes that touch the 'users' (taufiq/pgsql) connection before any
     * controller code runs -- Laravel's own 'unique' validation rule and the
     * Password broker both query it directly, bypassing DatabaseErrorHandler's
     * safeQuery() guard entirely. Pre-checking here (fast, cached fsockopen
     * probe via DatabaseConnectionChecker) stops those requests from blocking
     * a PHP-FPM worker on a raw PDO connect attempt that can hang far longer
     * than intended when the host is unreachable (e.g. powered off).
     *
     * @var string[]
     */
    private const USERS_DB_ROUTES = ['login', 'register', 'forgot-password', 'reset-password'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set per-request timeout. Skipped in tests: Pest's browser driver
        // dispatches every simulated page load through this same middleware
        // in one long-lived PHP process (never a fresh process per request
        // like real HTTP), and on Windows set_time_limit() is enforced even
        // under the CLI SAPI - so this reset was killing the whole browser
        // test process 60s after its last request, mid-assertion, regardless
        // of how fast the app itself actually responded.
        if (!app()->runningUnitTests()) {
            @set_time_limit(60);
        }

        // Pre-check the 'users' database before login, registration, or
        // either password-reset step -- all four hit it directly before
        // reaching controller code.
        if ($request->isMethod('POST') && $request->is(...self::USERS_DB_ROUTES)) {
            $checker = app(DatabaseConnectionChecker::class);

            if (!$checker->isConnected('users')) {
                Log::warning("Blocked {$request->path()} request while user database (taufiq) is offline");

                return redirect()->back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors([
                        'email' => 'Unable to process your request. The user database is currently offline. Please try again later or contact your administrator.',
                    ]);
            }
        }

        // Start request timing
        $startTime = microtime(true);

        try {
            $response = $next($request);

            // Log slow requests (over 10 seconds)
            $duration = microtime(true) - $startTime;
            if ($duration > 10) {
                Log::warning('Slow request detected', [
                    'url' => $request->fullUrl(),
                    'duration' => round($duration, 2) . 's',
                    'method' => $request->method(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            // Check if it's a timeout error
            if ($this->isTimeoutError($e)) {
                Log::error('Request timeout', [
                    'url' => $request->fullUrl(),
                    'duration' => round(microtime(true) - $startTime, 2) . 's',
                    'error' => $e->getMessage(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Request timeout',
                        'message' => 'The operation took too long. This may be due to offline databases.',
                    ], 504);
                }

                return redirect()->back()->with([
                    'error' => 'The operation timed out. Some databases may be offline. Please try again.',
                    'db_timeout' => true,
                ]);
            }

            // Re-throw if not a timeout error
            throw $e;
        }
    }

    /**
     * Check if the error is a timeout error
     *
     * @param \Throwable $e
     * @return bool
     */
    private function isTimeoutError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'maximum execution time') ||
               str_contains($message, 'timeout') ||
               str_contains($message, 'timed out') ||
               str_contains($message, 'time limit');
    }
}
