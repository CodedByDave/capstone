<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\ShopRegisterController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Services\LoginLogService;
use Illuminate\Support\Facades\Auth;

// Logout
Route::post('/logout', function (LoginLogService $loginLogService) {
    $user = Auth::user();

    if ($user) {
        $loginLogService->logLogout(
            userId:    $user->id,
            email:     $user->email,
            name:      $user->name,
            role:      $user->role,
            ip:        request()->ip(),
            userAgent: request()->userAgent() ?? '',
        );
    }

    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Guest Routes
Route::middleware(['guest'])->group(function () {

    Route::get('/login', [AuthController::class, 'show'])->name('login');

    Route::get('/register/shop', [ShopRegisterController::class, 'show'])->name('register.shop');
    Route::post('/register/shop', [ShopRegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.shop.store');

    Route::get('/register', [UserRegisterController::class, 'create'])->name('register.user');
    Route::post('/register', [UserRegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.user.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');

    Route::get('/verify-otp', [OtpVerificationController::class, 'show'])->name('otp.verify.page');
    Route::post('/verify-otp', [OtpVerificationController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('otp.verify');
    Route::post('/resend-otp', [OtpVerificationController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('otp.resend');
});
