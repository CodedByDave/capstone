<?php

use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ShopDataController;
use App\Http\Controllers\Shop\ShopOrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

//Routes for super admin
Route::prefix('admin')->middleware(['auth', 'verified', 'role:super_admin'])->group(function () {

    //Super admin dashboard
    Route::get('/dashboard', fn() => Inertia::render('admin/Dashboard'))->name('admin.dashboard');

    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
});

//Routes for shops
Route::prefix('shop')->middleware(['auth', 'role:owner'])->group(function () {

    //Shop Dashboard
    Route::get('/dashboard', [ShopOrderController::class, 'displayModules'])->name('shop.dashboard');

    // Route to get the authenticated owner's shop data
    Route::get('/data', [ShopDataController::class, 'getShop'])->name('shop.data');

    //Routes for payment
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [CheckoutController::class, 'cancel'])->name('payment.cancel');

});

//Routes for normal users
Route::prefix('user')->middleware(['auth', 'role:user'])->group(function () {

    //User dashboard
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
