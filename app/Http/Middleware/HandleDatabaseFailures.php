<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class HandleDatabaseFailures
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Clear any previous offline status if request succeeds
            session()->forget('db_offline');

            return $next($request);
        } catch (PDOException | QueryException $e) {
            // Log the database connection failure
            Log::warning('Database connection failed: ' . $e->getMessage());

            // Mark databases as offline in session
            session()->flash('db_offline', true);
            session()->flash('db_error', 'Unable to connect to remote databases. Displaying limited data.');

            // For AJAX requests, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Database temporarily unavailable',
                    'message' => 'Please check your internet connection and try again.'
                ], 503);
            }

            // For web requests, redirect back with error message
            return redirect()->back()->with([
                'error' => 'Database connection failed. Please check your internet connection.',
                'db_offline' => true
            ]);
        }
    }
}
