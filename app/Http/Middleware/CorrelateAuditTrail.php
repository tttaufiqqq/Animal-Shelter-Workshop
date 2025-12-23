<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelateAuditTrail
{
    /**
     * Handle an incoming request.
     *
     * Generate a unique correlation ID for this request to link related audit logs.
     * This is especially useful for multi-step operations that span multiple databases.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique correlation ID for this request
        $correlationId = Str::uuid()->toString();

        // Store in request attributes for controllers to access
        $request->attributes->set('correlation_id', $correlationId);

        // Store in session for multi-step operations (e.g., booking → payment → adoption flow)
        session(['audit_correlation_id' => $correlationId]);

        return $next($request);
    }
}
