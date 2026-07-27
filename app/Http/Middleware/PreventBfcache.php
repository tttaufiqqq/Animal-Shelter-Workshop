<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBfcache
{
    /**
     * Laravel's session middleware already sends "Cache-Control: no-cache,
     * private", but that alone doesn't exclude a page from Chrome's
     * back/forward cache (only "no-store" does) — so browser back/forward
     * can restore a frozen pre-navigation snapshot instead of a fresh
     * server render, which is stale for pages with per-request DB health
     * banners or client-side toggle state like the admin sidebar.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');

        return $response;
    }
}
