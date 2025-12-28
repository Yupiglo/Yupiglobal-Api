<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeAuthorizationHeader
{
    /**
     * Ensure Authorization header is in "Bearer <token>" format.
     * Some existing clients send the token without the Bearer prefix.
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header('Authorization');

        if (is_string($auth)) {
            $trimmed = trim($auth);

            if ($trimmed !== '' && stripos($trimmed, 'bearer ') !== 0) {
                $request->headers->set('Authorization', 'Bearer '.$trimmed);
            }
        }

        return $next($request);
    }
}
