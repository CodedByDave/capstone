<?php

use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Shop\CheckoutController;
use Illuminate\Support\Facades\Route;

// Checkout route
Route::post('/paymongo/checkout', [CheckoutController::class, 'create']);

// Driver delivery routes (token-authenticated — no user account required)
Route::prefix('driver')->group(function () {
    Route::get('/deliveries/{token}', [DriverController::class, 'show']);
    Route::patch('/deliveries/{token}/status', [DriverController::class, 'updateStatus']);
});
