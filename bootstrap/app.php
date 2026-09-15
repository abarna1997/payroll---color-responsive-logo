<?php

use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/adms/*',
            'iclock/*',
        ]);

        $middleware->append(\App\Http\Middleware\SecureHeaders::class);

        $middleware->alias([
            'role' => App\Http\Middleware::class.'\RoleMiddleware',
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'password_changed' => EnsurePasswordChanged::class,
            'is_employee' => \App\Http\Middleware\EnsureIsEmployee::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
