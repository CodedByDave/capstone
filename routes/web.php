<?php

use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Shop\EmployeeController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ShopDataController;
use App\Http\Controllers\Shop\ShopOrderController;
use App\Http\Controllers\Shop\ScheduleController;
use App\Http\Controllers\Shop\PermissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// ── Super admin routes ─────────────────────────────────────────────────────────

Route::prefix('admin')->middleware(['auth', 'verified', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('admin/Dashboard'))->name('admin.dashboard');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/settings/profile', fn() => Inertia::render('Admin/Settings'))->name('admin.settings');
});

// ── Shop owner routes ──────────────────────────────────────────────────────────

Route::prefix('shop')->middleware(['auth', 'verified', 'role:owner'])->group(function () {

    // Dashboard & misc
    Route::get('/dashboard', [ShopOrderController::class, 'displayModules'])->name('shop.dashboard');
    Route::get('/data', [ShopDataController::class, 'getShop'])->name('shop.data');
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [CheckoutController::class, 'cancel'])->name('payment.cancel');

    // ── Employee routes ──────────────────────────────────────────────────────
    Route::get('/employee',                   [EmployeeController::class, 'index'])->name('employee.index');
    Route::get('/employee/create',            [EmployeeController::class, 'create'])->name('employee.create');
    Route::get('/employee/archive',           [EmployeeController::class, 'archive'])->name('employee.archive');
    Route::get('/employee/import-template',   [EmployeeController::class, 'importTemplate'])->name('employee.import.template');

    Route::post('/employee',                  [EmployeeController::class, 'store'])->name('employee.store');
    Route::post('/employee/import',           [EmployeeController::class, 'import'])->name('employee.import');
    Route::post('/employee/bulk-restore',     [EmployeeController::class, 'bulkRestore'])->name('employee.bulk-restore');

    Route::get('/employee/{employee}',        [EmployeeController::class, 'show'])->name('employee.show');
    Route::get('/employee/{employee}/edit',   [EmployeeController::class, 'edit'])->name('employee.edit');

    Route::put('/employee/{employee}',        [EmployeeController::class, 'update'])->name('employee.update');
    Route::delete('/employee/{employee}',     [EmployeeController::class, 'destroy'])->name('employee.destroy');

    Route::post('/employee/{id}/restore',     [EmployeeController::class, 'restore'])->name('employee.restore');

    //Schedule Route
    Route::get('/employee/{employee}/schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
    Route::post('/employee/{employee}/schedule',       [ScheduleController::class, 'store'])->name('schedule.store');

    //Roles & Permission
    Route::get('/permission', [PermissionController::class, 'index'])->name('shop.permission');
    Route::post('/permission/update', [PermissionController::class, 'updatePermission'])->name('shop.permission.update');
    Route::post('/permission/roles', [PermissionController::class, 'storeRole'])->name('shop.permission.roles.store');
    Route::delete('/permission/roles/{role}', [PermissionController::class, 'destroyRole'])->name('shop.permission.roles.destroy');
    Route::post('/permission/employee/{employeeId}/toggle-role', [PermissionController::class, 'toggleEmployeeRole'])->name('shop.permission.employee.toggle');
});

// ── Staff routes ─────────────────────────────────────────────────────────
Route::prefix('/staff')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('staff/Dashboard'))->name('staff.dashboard');
});

// ── Normal user routes ─────────────────────────────────────────────────────────

Route::prefix('user')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
