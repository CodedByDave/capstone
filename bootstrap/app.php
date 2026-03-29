<?php

use App\Http\Middleware\CheckShopSubscription;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            CheckShopSubscription::class,
        ]);

        $middleware->alias([
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        $middleware->redirectGuestsTo(fn() => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if ($response->getStatusCode() === 403 && request()->header('X-Inertia')) {
                return redirect(request()->user()?->role === 'staff' ? '/staff/dashboard' : '/shop/dashboard')
                    ->with('toast', ['type' => 'error', 'message' => 'You do not have permission to access this page.']);
            }
            return $response;
        });
    })
    ->withProviders([
        AuthServiceProvider::class,
    ])
    ->create();
