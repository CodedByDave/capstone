<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\ShopRegisterController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\Auth\OtpVerificationController;

/**
 * Guest Routes not authenticated
 */
Route::middleware(['guest'])->group(function () {

    // Shop registration
    Route::get('/register/shop', [ShopRegisterController::class, 'create'])
        ->name('register.shop');

    Route::post('/register/shop', [ShopRegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.shop.store');
});

/**
 * Routes for authenticated user for otp
 */
Route::middleware(['auth'])->group(function () {

    // OTP verification page
    Route::get('/verify-otp', [OtpVerificationController::class, 'show'])
        ->name('otp.verify.page');

    // Verify OTP
    Route::post('/verify-otp', [OtpVerificationController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('otp.verify');

    // Resend OTP
    Route::post('/resend-otp', [OtpVerificationController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('otp.resend');
});
