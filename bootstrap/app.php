<?php

use App\Exceptions\ProtectedPlatformRoleException;
use App\Http\Middleware\CheckShopSubscription;
use App\Http\Middleware\EnsureShopNotArchived;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':global',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            CheckShopSubscription::class,
            EnsureShopNotArchived::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'owner.platform-permissions' => \App\Http\Middleware\EnforceOwnerPlatformPermissions::class,
            'shop.activity' => \App\Http\Middleware\TrackShopActivity::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ProtectedPlatformRoleException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['role' => $exception->getMessage()]);
        });

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
