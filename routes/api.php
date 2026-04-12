<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\MobileAuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Shop\CheckoutController;

// Checkout route
Route::post('/paymongo/checkout', [CheckoutController::class, 'create']);

// Driver delivery routes (token-authenticated — no user account required)
Route::prefix('driver')->group(function () {
    Route::get('/deliveries/{token}', [DriverController::class, 'show']);
    Route::patch('/deliveries/{token}/status', [DriverController::class, 'updateStatus']);
});

// Mobile auth routes
Route::prefix('mobile')->group(function () {
    Route::post('/register', [MobileAuthController::class, 'register']);
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [MobileAuthController::class, 'profile']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});
