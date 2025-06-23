<?php

use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\RolesAuthMiddleware;
use App\Http\Middleware\WebAuthMiddleware;

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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RolesAuthMiddleware::class,
            'api.auth' => ApiAuthMiddleware::class,
            'web.auth' => WebAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
