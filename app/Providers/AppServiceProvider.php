<?php

namespace App\Providers;

use App\Events\OrderPaymentRecorded;
use App\Events\OrderWeightConfirmed;
use App\Listeners\ApplyOrderStatusAutomation;
use App\Models\Employee;
use App\Models\Order;
use App\Models\ShopService;
use App\Observers\EmployeeObserver;
use App\Observers\OrderObserver;
use App\Repositories\Contracts\PlatformRoleRepositoryInterface;
use App\Repositories\Eloquent\PlatformRoleRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PlatformRoleRepositoryInterface::class,
            PlatformRoleRepository::class,
        );
    }

    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        Order::observe(OrderObserver::class);

        // Registered by hand: the method names are not `handle*`, so discovery will not add them twice.
        Event::listen(OrderWeightConfirmed::class, [ApplyOrderStatusAutomation::class, 'onWeightConfirmed']);
        Event::listen(OrderPaymentRecorded::class, [ApplyOrderStatusAutomation::class, 'onPaymentRecorded']);

        Route::bind('service', function ($value) {
            return ShopService::withTrashed()->findOrFail($value);
        });

        // Global rate limit: 200 requests/minute per IP — blocks simple HTTP floods
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(200)->by($request->ip());
        });
    }
}
