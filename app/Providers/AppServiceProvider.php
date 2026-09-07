<?php

namespace App\Providers;

use App\Models\Employee;
use App\Observers\EmployeeObserver;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\ShopService;;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        Order::observe(OrderObserver::class);

        Route::bind('service', function ($value) {
            return ShopService::withTrashed()->findOrFail($value);
        });

        // Global rate limit: 200 requests/minute per IP — blocks simple HTTP floods
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(200)->by($request->ip());
        });
    }
}
