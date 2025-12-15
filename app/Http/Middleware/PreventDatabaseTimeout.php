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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set per-request timeout
        @set_time_limit(60);

        // Check if this is a login request
        if ($request->is('login') && $request->isMethod('POST')) {
            // Pre-check if taufiq database (users) is online
            $checker = app(DatabaseConnectionChecker::class);

            if (!$checker->isConnected('taufiq')) {
                Log::warning('Login attempt while user database (taufiq) is offline');

                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'Unable to authenticate. The user database is currently offline. Please try again later or contact your administrator.',
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
