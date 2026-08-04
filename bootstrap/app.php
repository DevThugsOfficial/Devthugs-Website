<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'backend',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->registered(function (Application $app): void {
        $isVercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])
            || getenv('VERCEL') !== false;

        if ($isVercel) {
            $app->useStoragePath('/tmp/storage');
        }
    })
    ->create();

