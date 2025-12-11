<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Services\ForeignKeyValidator;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * User authentication uses Taufiq's database
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // Authenticate user from Taufiq's database
            $request->authenticate();

            // Regenerate session for security
            $request->session()->regenerate();

            // Get authenticated user from Taufiq's database
            $user = Auth::user();

            // Log successful login
            Log::info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            // Clear user cache (in case user data was cached)
            ForeignKeyValidator::clearUserCache($user->id);

            return redirect()->intended(route('welcome', absolute: false));

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'email' => $request->email,
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            // The LoginRequest will handle authentication failures
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     * Clears user cache and logs out
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Get user before logout
        $user = Auth::user();
        $userId = $user?->id;

        // Log logout
        if ($userId) {
            Log::info('User logged out', [
                'user_id' => $userId,
                'email' => $user->email,
            ]);

            // Clear user cache
            ForeignKeyValidator::clearUserCache($userId);
        }

        // Logout from Taufiq's database
        Auth::guard('web')->logout();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
