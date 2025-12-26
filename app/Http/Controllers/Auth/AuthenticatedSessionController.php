<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();

            $user = Auth::user();

            // CHECK: Account status before allowing login
            if ($user->isSuspended()) {
                Auth::guard('web')->logout();

                AuditService::logAuthentication(
                    'login_failed',
                    $user->email,
                    'Account suspended: ' . ($user->suspension_reason ?? 'No reason provided')
                );

                throw ValidationException::withMessages([
                    'email' => '🚫 Your account has been suspended. Reason: ' . ($user->suspension_reason ?? 'Contact administrator') . '. Visit our Contact Page to appeal: ' . route('contact'),
                ]);
            }

            if ($user->isLocked()) {
                Auth::guard('web')->logout();

                $lockedUntil = $user->locked_until->format('Y-m-d H:i:s');

                AuditService::logAuthentication(
                    'login_failed',
                    $user->email,
                    'Account locked until ' . $lockedUntil
                );

                throw ValidationException::withMessages([
                    'email' => '🔒 Your account is temporarily locked until ' . $lockedUntil . '. Reason: ' . ($user->lock_reason ?? 'Security measure') . '. Need help? Visit: ' . route('contact'),
                ]);
            }

            // Reset failed login attempts on successful login
            $user->update([
                'failed_login_attempts' => 0,
                'last_failed_login_at' => null,
            ]);

            $request->session()->regenerate();

            // AUDIT: Successful login
            AuditService::logAuthentication('login_success', $user->email);

            return redirect()->intended(route('welcome', absolute: false));
        } catch (ValidationException $e) {
            // AUDIT: Failed login attempt
            $errorMessage = $e->getMessage();

            // Check if it's a rate limit error or invalid credentials
            if (str_contains($errorMessage, 'throttle')) {
                $error = 'Too many login attempts. Account temporarily locked.';
            } elseif (str_contains($errorMessage, 'suspended') || str_contains($errorMessage, 'locked')) {
                // Already handled above, just rethrow
                throw $e;
            } else {
                $error = 'Invalid credentials';

                // Increment failed login attempts for the user
                $user = \App\Models\User::where('email', $request->email)->first();
                if ($user) {
                    $user->increment('failed_login_attempts');
                    $user->update(['last_failed_login_at' => now()]);
                }
            }

            AuditService::logAuthentication(
                'login_failed',
                $request->email,
                $error
            );

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // AUDIT: User logout
        if ($user) {
            AuditService::logAuthentication('logout', $user->email);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
