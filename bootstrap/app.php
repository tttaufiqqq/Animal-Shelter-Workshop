<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // cloudflared and nginx both run on the same app-server box, so every
        // request php-fpm sees arrives from 127.0.0.1 regardless of the real
        // visitor — trust that single hop so $request->ip()/isSecure() reflect
        // the real client via X-Forwarded-For/-Proto instead of the proxy's own.
        $middleware->trustProxies(at: [
            '127.0.0.1',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\PreventDatabaseTimeout::class,
            \App\Http\Middleware\InjectDatabaseStatus::class,
            \App\Http\Middleware\HandleDatabaseFailures::class,
            \App\Http\Middleware\CorrelateAuditTrail::class, // Audit trail correlation ID generation
            \App\Http\Middleware\RequirePasswordChange::class, // Force password change when required
            // \App\Http\Middleware\CheckDatabaseHealth::class, // DISABLED: Database backup system (uncomment to enable)
        ]);

        // ToyyibPay's server-to-server callback can't carry a CSRF token.
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
        ]);

        // Register Spatie Permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
