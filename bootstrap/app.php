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
        $middleware->web(append: [
            \App\Http\Middleware\PreventDatabaseTimeout::class,
            \App\Http\Middleware\InjectDatabaseStatus::class,
            \App\Http\Middleware\HandleDatabaseFailures::class,
            \App\Http\Middleware\CorrelateAuditTrail::class, // Audit trail correlation ID generation
            \App\Http\Middleware\RequirePasswordChange::class, // Force password change when required
            // \App\Http\Middleware\CheckDatabaseHealth::class, // DISABLED: Database backup system (uncomment to enable)
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
