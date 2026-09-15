<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeaders
{
    /**
     * Handle an incoming request and add secure HTTP headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('X-Content-Type-Options', 'nosniff');
            if ($request->isSecure()) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        return $response;
    }
}
