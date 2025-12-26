<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user requires password change
            if ($user->require_password_reset) {
                // Allow access to password change route and logout
                $allowedRoutes = [
                    'password.change',
                    'password.update',
                    'logout',
                ];

                // If not on an allowed route, redirect to password change page
                if (!$request->routeIs(...$allowedRoutes)) {
                    return redirect()->route('password.change')
                        ->with('warning', 'You must change your password before continuing.');
                }
            }
        }

        return $next($request);
    }
}
