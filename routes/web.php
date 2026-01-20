<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


//Routes for super admin
Route::prefix('admin')->middleware(['auth', 'role:super_admin'])->group(function (){

    //Super admin dashboard
    Route::get('/dashboard', fn() => Inertia::render('admin/Dashboard'))->name('admin.dashboard');
});

//Routes for shops
Route::prefix('shop')->middleware(['auth', 'role:owner'])->group(function (){

    //Shop Dashboard
    Route::get('/dashboard', fn() => Inertia::render('shop/Dashboard'))->name('shop.dashboard');
});

//Routes for normal users
Route::prefix('user')->middleware(['auth', 'role:user'])->group(function (){

    //User dashboard
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
