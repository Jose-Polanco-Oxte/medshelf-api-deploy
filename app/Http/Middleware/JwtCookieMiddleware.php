<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtCookieMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $authType = $request->header('X-Auth-Type', 'Cookie');

        if ($authType === 'JWT') {
            // JWT mode: the caller provides Authorization: Bearer <token> directly.
            // Nothing to inject — the header is already present.
            return $next($request);
        }

        // Cookie mode (default): extract the token from the HttpOnly cookie
        // and inject it as a Bearer token so the JWT guard can find it.
        $token = $request->cookie('access_token');

        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}