<?php

use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ShopDataController;
use App\Http\Controllers\Shop\ShopOrderController;
use App\Http\Controllers\Shop\Employee\EmployeeController;
use App\Http\Controllers\Shop\Employee\ScheduleController;
use App\Http\Controllers\Shop\Employee\PermissionController;
use App\Http\Controllers\Shop\Employee\ActivityLogsController;
use App\Http\Controllers\Shop\Employee\BranchController;
use App\Http\Controllers\Staff\StaffDashboardController;
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
    Route::get('/settings/profile', fn() => Inertia::render('Admin/Settings'))->name('admin.settings');

    // Shop Management
    Route::get('/shop',             [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/{shop}',      [ShopController::class, 'show'])->name('shop.show');
    Route::get('/shop/{shop}/edit', [ShopController::class, 'edit'])->name('shop.edit');
    Route::put('/shop/{shop}',      [ShopController::class, 'update'])->name('shop.update');
    Route::delete('/shop/{shop}',   [ShopController::class, 'destroy'])->name('shop.destroy');
});

// ── Shop owner routes ──────────────────────────────────────────────────────────

Route::prefix('shop')->middleware(['auth', 'verified', 'role:owner'])->group(function () {

    // Dashboard & misc
    Route::get('/dashboard', [ShopOrderController::class, 'displayModules'])->name('shop.dashboard');
    Route::get('/data', [ShopDataController::class, 'getShop'])->name('shop.data');
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [CheckoutController::class, 'cancel'])->name('payment.cancel');

    // Activity Logs
    Route::get('/logs', [ActivityLogsController::class, 'index'])->name('logs.index');

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

    // Schedule
    Route::get('/employee/{employee}/schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
    Route::post('/employee/{employee}/schedule',       [ScheduleController::class, 'store'])->name('schedule.store');

    // ── Branch routes ────────────────────────────────────────────────────────
    Route::get('/branch',                 [BranchController::class, 'index']  )->name('branch.index');
    Route::get('/branch/create',          [BranchController::class, 'create'] )->name('branch.create');
    Route::get('/branch/archive',         [BranchController::class, 'archive'])->name('branch.archive');
    Route::post('/branch',                [BranchController::class, 'store']  )->name('branch.store');
    Route::get('/branch/{branch}/edit',   [BranchController::class, 'edit']   )->name('branch.edit');
    Route::put('/branch/{branch}',        [BranchController::class, 'update'] )->name('branch.update');
    Route::delete('/branch/{branch}',     [BranchController::class, 'destroy'])->name('branch.destroy');
    Route::post('/branch/{id}/restore',   [BranchController::class, 'restore'])->name('branch.restore');

    // Roles & Permission
    Route::get('/permission',                                    [PermissionController::class, 'index'])->name('shop.permission');
    Route::post('/permission/update',                            [PermissionController::class, 'updatePermission'])->name('shop.permission.update');
    Route::post('/permission/roles',                             [PermissionController::class, 'storeRole'])->name('shop.permission.roles.store');
    Route::delete('/permission/roles/{role}',                    [PermissionController::class, 'destroyRole'])->name('shop.permission.roles.destroy');
    Route::post('/permission/employee/{employeeId}/toggle-role', [PermissionController::class, 'toggleEmployeeRole'])->name('shop.permission.employee.toggle');
});

Route::prefix('staff')->middleware(['auth', 'verified', 'role:staff'])->group(function () {

    Route::get('/dashboard', [StaffDashboardController::class, 'index'])
        ->name('staff.dashboard');

    // Employee Management
    Route::middleware('permission:Employee Management,view')->group(function () {
        Route::get('/employee',            [EmployeeController::class, 'index'])->name('staff.employee.index');
        Route::get('/employee/{employee}', [EmployeeController::class, 'show'])->name('staff.employee.show');
        Route::get('/branch',              [BranchController::class, 'index'])->name('staff.branch.index');
    });

    Route::middleware('permission:Employee Management,create')->group(function () {
        Route::get('/employee/create',  [EmployeeController::class, 'create'])->name('staff.employee.create');
        Route::post('/employee',        [EmployeeController::class, 'store'])->name('staff.employee.store');
    });

    Route::middleware('permission:Employee Management,update')->group(function () {
        Route::get('/employee/{employee}/edit', [EmployeeController::class, 'edit'])->name('staff.employee.edit');
        Route::put('/employee/{employee}',      [EmployeeController::class, 'update'])->name('staff.employee.update');
    });

    Route::middleware('permission:Employee Management,archive')->group(function () {
        Route::delete('/employee/{employee}', [EmployeeController::class, 'destroy'])->name('staff.employee.destroy');
    });

    Route::middleware('permission:Employee Management,import')->group(function () {
        Route::post('/employee/import', [EmployeeController::class, 'import'])->name('staff.employee.import');
    });
});

// ── Normal user routes ─────────────────────────────────────────────────────────

Route::prefix('user')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
