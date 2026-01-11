<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\ShopRegisterController;

Route::middleware(['guest'])->group(function () {

    // Shop registration
    Route::get('/register/shop', [ShopRegisterController::class, 'create'])->name('register.shop');
    Route::post('/register/shop', [ShopRegisterController::class, 'store'])->middleware('throttle: 5,1')
        ->name('register.shop.store');
});
