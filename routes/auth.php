<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\ShopRegisterController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/**
 * Guest Routes - not authenticated
 */
Route::middleware(['guest'])->group(function () {

    //Login
    Route::get('/login', [AuthController::class, 'show'])->name('login.user');

    // Shop registration
    Route::get('/register/shop', [ShopRegisterController::class, 'show'])
        ->name('register.shop');

    Route::post('/register/shop', [ShopRegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.shop.store');

    //Forgot password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    // Password Reset
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'update'])
        ->name('password.update');

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
