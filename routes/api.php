<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\MobileAuthController;
use App\Http\Controllers\Shop\CheckoutController;

// Checkout route
Route::post('/paymongo/checkout', [CheckoutController::class, 'create']);

// Mobile auth routes
Route::prefix('mobile')->group(function () {
    Route::post('/register', [MobileAuthController::class, 'register']);
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [MobileAuthController::class, 'profile']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});
