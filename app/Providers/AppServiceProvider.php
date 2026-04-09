<?php

namespace App\Providers;

use App\Models\Employee;
use App\Observers\EmployeeObserver;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\ShopServicePricing;;
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
            return ShopServicePricing::withTrashed()->findOrFail($value);
        });
    }
}
