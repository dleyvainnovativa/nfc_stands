<?php

use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Role guard alias used across web routes.
        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);

        // The theme cookie is written client-side (plain text) so the server
        // can render the correct <html data-theme> on first paint. Exclude it
        // from cookie encryption, otherwise decryption fails and it's dropped.
        $middleware->encryptCookies(except: ['theme']);

        // Where the built-in auth/guest middleware send users.
        $middleware->redirectTo(
            guests: '/login',
            users: '/dashboard',
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
