<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->force_password_change) {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;

            if ($routeName !== 'password.change' && $routeName !== 'password.change.post' && $routeName !== 'logout') {
                return redirect()->route('password.change')->with('error', 'You must change your default password before accessing the system.');
            }
        }

        return $next($request);
    }
}
