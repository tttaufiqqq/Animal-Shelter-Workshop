<?php

namespace App\Listeners;

use App\Jobs\CreateAuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event)
    {
        CreateAuditLog::dispatch([
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'user_name' => $event->user->name,
            'action_type' => 'login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'metadata' => [
                'guard' => $event->guard,
            ],
        ]);
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event)
    {
        CreateAuditLog::dispatch([
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'user_name' => $event->user->name,
            'action_type' => 'logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'metadata' => [
                'guard' => $event->guard,
            ],
        ]);
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailed(Failed $event)
    {
        CreateAuditLog::dispatch([
            'user_id' => null,
            'user_email' => $event->credentials['email'] ?? null,
            'user_name' => null,
            'action_type' => 'failed_login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'metadata' => [
                'guard' => $event->guard,
                'reason' => 'Invalid credentials',
            ],
        ]);
    }

    /**
     * Handle account lockout events.
     */
    public function handleLockout(Lockout $event)
    {
        CreateAuditLog::dispatch([
            'user_id' => null,
            'user_email' => request()->email ?? null,
            'user_name' => null,
            'action_type' => 'failed_login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'metadata' => [
                'reason' => 'Account locked out (too many attempts)',
            ],
        ]);
    }
}
