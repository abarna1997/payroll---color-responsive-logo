<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If the user's role is in the authorized roles list, proceed
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. You do not have permission to view this resource.');
    }
}
