<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::prefix('admin')->middleware(['auth', 'role:super_admin'])->group(function (){
    Route::get('/dashboard', fn() => Inertia::render('admin/Dashboard'))->name('dashboard');
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
