<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureIsEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user || !$user->employee) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Employee profile required.'], 403);
            }
            abort(403, 'Access denied. You must have an active employee profile to access this portal.');
        }

        return $next($request);
    }
}
