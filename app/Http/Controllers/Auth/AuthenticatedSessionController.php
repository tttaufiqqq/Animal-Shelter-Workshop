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

            $request->session()->regenerate();

            // AUDIT: Successful login
            AuditService::logAuthentication('login_success', Auth::user()->email);

            return redirect()->intended(route('welcome', absolute: false));
        } catch (ValidationException $e) {
            // AUDIT: Failed login attempt
            $errorMessage = $e->getMessage();

            // Check if it's a rate limit error or invalid credentials
            if (str_contains($errorMessage, 'throttle')) {
                $error = 'Too many login attempts. Account temporarily locked.';
            } else {
                $error = 'Invalid credentials';
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
