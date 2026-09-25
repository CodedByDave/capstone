<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\ShopService;
use App\Observers\EmployeeObserver;
use App\Observers\OrderObserver;
<<<<<<< HEAD
=======
use App\Repositories\Contracts\PlatformRoleRepositoryInterface;
use App\Repositories\Eloquent\PlatformRoleRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
<<<<<<< HEAD

=======
        $this->app->bind(
            PlatformRoleRepositoryInterface::class,
            PlatformRoleRepository::class,
        );
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        Order::observe(OrderObserver::class);
    }
}
