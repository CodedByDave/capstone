<?php

use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ShopDataController;
use App\Http\Controllers\Shop\ShopDashboardController;
use App\Http\Controllers\Shop\ReportsController;
use App\Http\Controllers\Shop\Employee\EmployeeController;
use App\Http\Controllers\Shop\Employee\ScheduleController;
use App\Http\Controllers\Shop\Employee\PermissionController;
use App\Http\Controllers\Shop\Employee\PayrollController;
use App\Http\Controllers\Shop\Employee\ActivityLogsController;
use App\Http\Controllers\Shop\Employee\AttendanceController;
use App\Http\Controllers\Shop\Employee\BranchController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Shop\Inventory\InventoryController;
use App\Http\Controllers\Shop\Inventory\SupplierController;
use App\Http\Controllers\Shop\Inventory\InventoryCategoryController;
use App\Http\Controllers\Shop\Inventory\LowStockAlertController;
use App\Http\Controllers\Shop\Operations\ShopOrderController;
use App\Http\Controllers\Shop\Operations\ShopServiceController;
use App\Http\Controllers\Shop\Operations\PromotionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('landing');

Route::get('/shop/checkout/{plan}',  [CheckoutController::class, 'show']);
Route::post('/checkout/select',      [CheckoutController::class, 'select']);
Route::get('/plans', [CheckoutController::class, 'plans'])->name('plans');

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/confirm',  [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    Route::post('/checkout/process', [CheckoutController::class, 'checkout'])->name('checkout.process');
});

// ── Super admin routes ─────────────────────────────────────────────────────────

Route::prefix('admin')->middleware(['auth', 'verified', 'role:super_admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/settings/profile', fn() => Inertia::render('Admin/Settings'))->name('admin.settings');

    Route::get('/shop',                 [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/{shop}',          [ShopController::class, 'show'])->name('shop.show');
    Route::get('/shop/{shop}/edit',     [ShopController::class, 'edit'])->name('shop.edit');
    Route::put('/shop/{shop}',          [ShopController::class, 'update'])->name('shop.update');
    Route::delete('/shop/{shop}',       [ShopController::class, 'destroy'])->name('shop.destroy');
    Route::post('/shop/{shop}/disable', [ShopController::class, 'disable'])->name('shop.disable');
    Route::post('/shop/{shop}/enable',  [ShopController::class, 'enable'])->name('shop.enable');


    // ── Users ─────────────────────────────────────────────────────────────────────
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/',              [UserController::class, 'index'])->name('index');
        Route::post('/bulk-archive', [UserController::class, 'bulkArchive'])->name('bulk-archive');

        Route::prefix('archive')->name('archive.')->group(function () {
            Route::get('/',              [UserController::class, 'archiveIndex'])->name('index');
            Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
            Route::post('/bulk-restore', [UserController::class, 'bulkRestore'])->name('bulk-restore');
            Route::delete('/{id}',       [UserController::class, 'forceDelete'])->name('force-delete');
        });

        Route::get('/{user}',        [UserController::class, 'show'])->name('show');
        Route::delete('/{user}',     [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('login-logs')->name('admin.login-logs.')->group(function () {
        Route::get('/',               [LoginLogController::class, 'index'])->name('index');
        Route::delete('/{loginLog}',  [LoginLogController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-archive',  [LoginLogController::class, 'bulkArchive'])->name('bulk-archive');

        Route::prefix('archive')->name('archive.')->group(function () {
            Route::get('/',              [LoginLogController::class, 'archiveIndex'])->name('index');
            Route::post('/{id}/restore', [LoginLogController::class, 'restore'])->name('restore');
            Route::post('/bulk-restore', [LoginLogController::class, 'bulkRestore'])->name('bulk-restore');
            Route::delete('/{id}',       [LoginLogController::class, 'forceDelete'])->name('force-delete');
        });
    });

    // Orders
    Route::prefix('orders')->name('admin.orders.')->group(function () {
        Route::get('/',              [OrderController::class, 'index'])->name('index');
        Route::get('/{id}',          [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [OrderController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject',  [OrderController::class, 'reject'])->name('reject');
    });

    Route::get('/kyc-file', [OrderController::class, 'serveKyc'])->name('admin.kyc-file');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
});

// ── Shop owner routes ──────────────────────────────────────────────────────────

Route::prefix('shop')->middleware(['auth', 'verified', 'role:owner'])->group(function () {

    Route::get('/dashboard', [ShopDashboardController::class, 'index'])->name('shop.dashboard');
    Route::get('/data',      [ShopDataController::class, 'getShop'])->name('shop.data');

    Route::post('/checkout',       [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel',  [CheckoutController::class, 'cancel'])->name('payment.cancel');

    Route::get('/logs', [ActivityLogsController::class, 'index'])->name('logs.index');

    // ── Employee ──────────────────────────────────────────────────────────────
    Route::get('/employee/create',          [EmployeeController::class, 'create'])->name('employee.create');
    Route::get('/employee/archive',         [EmployeeController::class, 'archive'])->name('employee.archive');
    Route::get('/employee/import-template', [EmployeeController::class, 'importTemplate'])->name('employee.import.template');
    Route::post('/employee/import',         [EmployeeController::class, 'import'])->name('employee.import');
    Route::post('/employee/bulk-restore',   [EmployeeController::class, 'bulkRestore'])->name('employee.bulk-restore');

    Route::get('/employee',                 [EmployeeController::class, 'index'])->name('employee.index');
    Route::post('/employee',                [EmployeeController::class, 'store'])->name('employee.store');
    Route::get('/employee/{employee}',      [EmployeeController::class, 'show'])->name('employee.show');
    Route::get('/employee/{employee}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
    Route::put('/employee/{employee}',      [EmployeeController::class, 'update'])->name('employee.update');
    Route::delete('/employee/{employee}',   [EmployeeController::class, 'destroy'])->name('employee.destroy');
    Route::post('/employee/{id}/restore',   [EmployeeController::class, 'restore'])->name('employee.restore');

    // Schedule
    Route::put('/employee/{employee}/schedule', [ScheduleController::class, 'sync']);
    Route::delete('/employee/{employee}/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

    // Attendance
    Route::get('/attendance',              [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance',             [AttendanceController::class, 'store'])->name('attendance.store');
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

    // Payroll
    Route::get('/payroll',                          [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll',                         [PayrollController::class, 'store'])->name('payroll.store');
    Route::put('/payroll/settings',                 [PayrollController::class, 'updateSettings'])->name('payroll.settings');
    Route::get('/payroll/{payroll}',                [PayrollController::class, 'show'])->name('payroll.show');
    Route::put('/payroll/{payrollItem}/item',       [PayrollController::class, 'updateItem'])->name('payroll.item.update');
    Route::post('/payroll/{payroll}/recalculate',   [PayrollController::class, 'recalculate'])->name('payroll.recalculate');
    Route::post('/payroll/{payroll}/finalize',      [PayrollController::class, 'finalize'])->name('payroll.finalize');
    Route::delete('/payroll/{payroll}',             [PayrollController::class, 'destroy'])->name('payroll.destroy');

    // ── Branch ────────────────────────────────────────────────────────────────
    Route::get('/branch/create',        [BranchController::class, 'create'])->name('branch.create');
    Route::get('/branch/archive',       [BranchController::class, 'archive'])->name('branch.archive');
    Route::post('/branch/{id}/restore', [BranchController::class, 'restore'])->name('branch.restore');

    Route::get('/branch',               [BranchController::class, 'index'])->name('branch.index');
    Route::post('/branch',              [BranchController::class, 'store'])->name('branch.store');
    Route::get('/branch/{branch}/edit', [BranchController::class, 'edit'])->name('branch.edit');
    Route::put('/branch/{branch}',      [BranchController::class, 'update'])->name('branch.update');
    Route::delete('/branch/{branch}',   [BranchController::class, 'destroy'])->name('branch.destroy');

    // ── Roles & Permissions ───────────────────────────────────────────────────
    Route::get('/permission',                                    [PermissionController::class, 'index'])->name('shop.permission');
    Route::post('/permission/update',                            [PermissionController::class, 'updatePermission'])->name('shop.permission.update');
    Route::post('/permission/roles',                             [PermissionController::class, 'storeRole'])->name('shop.permission.roles.store');
    Route::delete('/permission/roles/{role}',                    [PermissionController::class, 'destroyRole'])->name('shop.permission.roles.destroy');
    Route::post('/permission/employee/{employeeId}/toggle-role', [PermissionController::class, 'toggleEmployeeRole'])->name('shop.permission.employee.toggle');

    // ── Inventory Categories ──────────────────────────────────────────────────
    Route::get('/inventory/category',                          [InventoryCategoryController::class, 'index'])->name('inventory.category.index');
    Route::post('/inventory/category',                         [InventoryCategoryController::class, 'store'])->name('inventory.category.store');
    Route::get('/inventory/category/{inventoryCategory}/edit', [InventoryCategoryController::class, 'edit'])->name('inventory.category.edit');
    Route::put('/inventory/category/{inventoryCategory}',      [InventoryCategoryController::class, 'update'])->name('inventory.category.update');
    Route::delete('/inventory/category/{inventoryCategory}',   [InventoryCategoryController::class, 'destroy'])->name('inventory.category.destroy');
    Route::get('/inventory/category/archive',        [InventoryCategoryController::class, 'archive'])->name('inventory.category.archive');
    Route::post('/inventory/category/{id}/restore',  [InventoryCategoryController::class, 'restore'])->name('inventory.category.restore');

    // ── Low Stock Alerts ──────────────────────────────────────────────────────
    Route::get('/inventory/alerts',                    [LowStockAlertController::class, 'index'])->name('inventory.alerts.index');
    Route::post('/inventory/alerts/mark-all-read',     [LowStockAlertController::class, 'markAllRead'])->name('inventory.alerts.mark-all-read');
    Route::patch('/inventory/alerts/{lowStockAlert}',  [LowStockAlertController::class, 'update'])->name('inventory.alerts.update');

    // ── Inventory ─────────────────────────────────────────────────────────────
    Route::get('/inventory',                     [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create',              [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory',                    [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}',         [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{inventory}/edit',    [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{inventory}',         [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{inventory}',      [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');

    // ── Suppliers ─────────────────────────────────────────────────────────────
    Route::get('/supplier',                 [SupplierController::class, 'index'])->name('supplier.index');
    Route::get('/supplier/create',          [SupplierController::class, 'create'])->name('supplier.create');
    Route::post('/supplier',                [SupplierController::class, 'store'])->name('supplier.store');
    Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::put('/supplier/{supplier}',      [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{supplier}',   [SupplierController::class, 'destroy'])->name('supplier.destroy');
    Route::get('/supplier/archive',         [SupplierController::class, 'archive'])->name('supplier.archive');
    Route::post('/supplier/{id}/restore',   [SupplierController::class, 'restore'])->name('supplier.restore');

    // Operations Routes
    Route::get('operations/orders',                   [ShopOrderController::class, 'index'])->name('shop.orders.index');
    Route::get('operations/orders/create',            [ShopOrderController::class, 'create'])->name('shop.orders.create');
    Route::post('operations/orders',                  [ShopOrderController::class, 'store'])->name('shop.orders.store');
    Route::get('operations/orders/{order}',           [ShopOrderController::class, 'show'])->name('shop.orders.show');
    Route::get('operations/orders/{order}/edit',      [ShopOrderController::class, 'edit'])->name('shop.orders.edit');
    Route::put('operations/orders/{order}',           [ShopOrderController::class, 'update'])->name('shop.orders.update');
    Route::delete('operations/orders/{order}',        [ShopOrderController::class, 'destroy'])->name('shop.orders.destroy');

    //Services and pricing
    Route::get('operations/services',                          [ShopServiceController::class, 'index'])->name('shop.services.index');
    Route::get('operations/services/create',                   [ShopServiceController::class, 'create'])->name('shop.services.create');
    Route::post('operations/services',                         [ShopServiceController::class, 'store'])->name('shop.services.store');
    Route::post('operations/services/restore-all',             [ShopServiceController::class, 'restoreAll'])->name('shop.services.restore-all');  // ← before {service}
    Route::get('operations/services/{service}/edit',           [ShopServiceController::class, 'edit'])->name('shop.services.edit');
    Route::put('operations/services/{service}',                [ShopServiceController::class, 'update'])->name('shop.services.update');
    Route::delete('operations/services/{service}',             [ShopServiceController::class, 'destroy'])->name('shop.services.destroy');
    Route::patch('operations/services/{service}/toggle',       [ShopServiceController::class, 'toggleActive'])->name('shop.services.toggle');
    Route::patch('operations/services/{service}/restore',      [ShopServiceController::class, 'restore'])->name('shop.services.restore');

    // Promotions
    Route::get('operations/promos',                             [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('operations/promos/create',                      [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('operations/promos',                            [PromotionController::class, 'store'])->name('promotions.store');
    Route::get('operations/promos/{promotion}/edit',            [PromotionController::class, 'edit'])->name('promotions.edit');
    Route::put('operations/promos/{promotion}',                 [PromotionController::class, 'update'])->name('promotions.update');
    Route::patch('operations/promos/{promotion}/toggle',        [PromotionController::class, 'toggleActive'])->name('promotions.toggle');
    Route::delete('operations/promos/{promotion}',              [PromotionController::class, 'destroy'])->name('promotions.destroy');
    Route::patch('operations/promos/{promotion}/restore',       [PromotionController::class, 'restore'])->name('promotions.restore');

    Route::post('operations/orders/restore-all',        [ShopOrderController::class, 'restoreAll'])->name('shop.orders.restore-all');
Route::patch('operations/orders/{order}/restore',   [ShopOrderController::class, 'restore'])->name('shop.orders.restore');

    // Dedicated status & payment update endpoints
    Route::patch('orders/{order}/status',  [ShopOrderController::class, 'updateStatus'])->name('status');
    Route::patch('orders/{order}/payment', [ShopOrderController::class, 'updatePayment'])->name('payment');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',          [ReportsController::class, 'overview'])->name('overview');
        Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
        Route::get('/employee',  [ReportsController::class, 'employee'])->name('employee');
    });
});

// ── Staff routes ───────────────────────────────────────────────────────────────

Route::prefix('staff')->middleware(['auth', 'verified', 'role:staff'])->group(function () {

    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');

    // ── Employee ──────────────────────────────────────────────────────────────
    Route::middleware('permission:HRM,view')->group(function () {
        Route::get('/employee', [EmployeeController::class, 'index'])->name('staff.employee.index');
        Route::get('/employee/{employee}', [EmployeeController::class, 'show'])->name('staff.employee.show');
    });
    Route::middleware('permission:HRM,create')->group(function () {
        Route::get('/employee/create', [EmployeeController::class, 'create'])->name('staff.employee.create');
        Route::post('/employee',       [EmployeeController::class, 'store'])->name('staff.employee.store');
    });
    Route::middleware('permission:HRM,archive')->group(function () {
        Route::get('/employee/archive',       [EmployeeController::class, 'archive'])->name('staff.employee.archive');
        Route::post('/employee/bulk-restore', [EmployeeController::class, 'bulkRestore'])->name('staff.employee.bulk-restore');
        Route::delete('/employee/{employee}', [EmployeeController::class, 'destroy'])->name('staff.employee.destroy');
        Route::post('/employee/{id}/restore', [EmployeeController::class, 'restore'])->name('staff.employee.restore');
    });
    Route::middleware('permission:HRM,import')->group(function () {
        Route::get('/employee/import-template', [EmployeeController::class, 'importTemplate'])->name('staff.employee.import.template');
        Route::post('/employee/import',         [EmployeeController::class, 'import'])->name('staff.employee.import');
    });
    Route::middleware('permission:HRM,update')->group(function () {
        Route::get('/employee/{employee}/edit', [EmployeeController::class, 'edit'])->name('staff.employee.edit');
        Route::put('/employee/{employee}',      [EmployeeController::class, 'update'])->name('staff.employee.update');
    });

    // ── Schedule ──────────────────────────────────────────────────────────────
    Route::middleware('permission:HRM,update')->group(function () {
        Route::post('/employee/{employee}/schedule',              [ScheduleController::class, 'store'])->name('staff.schedule.store');
        Route::put('/employee/{employee}/schedule/{schedule}',    [ScheduleController::class, 'update'])->name('staff.schedule.update');
        Route::delete('/employee/{employee}/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('staff.schedule.destroy');
    });

    // ── Branch ────────────────────────────────────────────────────────────────
    Route::middleware('permission:HRM,view')->group(function () {
        Route::get('/branch', [BranchController::class, 'index'])->name('staff.branch.index');
    });
    Route::middleware('permission:HRM,create')->group(function () {
        Route::get('/branch/create', [BranchController::class, 'create'])->name('staff.branch.create');
        Route::post('/branch',       [BranchController::class, 'store'])->name('staff.branch.store');
    });
    Route::middleware('permission:HRM,archive')->group(function () {
        Route::get('/branch/archive',       [BranchController::class, 'archive'])->name('staff.branch.archive');
        Route::post('/branch/{id}/restore', [BranchController::class, 'restore'])->name('staff.branch.restore');
        Route::delete('/branch/{branch}',   [BranchController::class, 'destroy'])->name('staff.branch.destroy');
    });
    Route::middleware('permission:HRM,update')->group(function () {
        Route::get('/branch/{branch}/edit', [BranchController::class, 'edit'])->name('staff.branch.edit');
        Route::put('/branch/{branch}',      [BranchController::class, 'update'])->name('staff.branch.update');
    });

    // ── Inventory Categories ──────────────────────────────────────────────────
    Route::middleware('permission:Inventory Management,view')->group(function () {
        Route::get('/inventory/category',         [InventoryCategoryController::class, 'index'])->name('staff.inventory.category.index');
        Route::get('/inventory/category/archive', [InventoryCategoryController::class, 'archive'])->name('staff.inventory.category.archive');
    });
    Route::middleware('permission:Inventory Management,create')->group(function () {
        Route::post('/inventory/category', [InventoryCategoryController::class, 'store'])->name('staff.inventory.category.store');
    });
    Route::middleware('permission:Inventory Management,archive')->group(function () {
        Route::post('/inventory/category/{id}/restore',          [InventoryCategoryController::class, 'restore'])->name('staff.inventory.category.restore');
        Route::delete('/inventory/category/{inventoryCategory}', [InventoryCategoryController::class, 'destroy'])->name('staff.inventory.category.destroy');
    });

    Route::middleware('permission:Inventory Management,update')->group(function () {
        Route::get('/inventory/category/{inventoryCategory}/edit', [InventoryCategoryController::class, 'edit'])->name('staff.inventory.category.edit');
        Route::put('/inventory/category/{inventoryCategory}',      [InventoryCategoryController::class, 'update'])->name('staff.inventory.category.update');
    });

    // ── Inventory ─────────────────────────────────────────────────────────────
    Route::middleware('permission:Inventory Management,view')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('staff.inventory.index');
    });
    Route::middleware('permission:Inventory Management,create')->group(function () {
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('staff.inventory.create');
        Route::post('/inventory',       [InventoryController::class, 'store'])->name('staff.inventory.store');
    });

    Route::middleware('permission:Inventory Management,update')->group(function () {
        Route::get('/inventory/{inventory}/edit',    [InventoryController::class, 'edit'])->name('staff.inventory.edit');
        Route::put('/inventory/{inventory}',         [InventoryController::class, 'update'])->name('staff.inventory.update');
        Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'adjustStock'])->name('staff.inventory.adjust');
    });

    Route::middleware('permission:Inventory Management,archive')->group(function () {
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('staff.inventory.destroy');
    });

    // ── Low Stock Alerts ──────────────────────────────────────────────────────
    Route::middleware('permission:Inventory Management,view')->group(function () {
        Route::get('/inventory/alerts',                   [LowStockAlertController::class, 'index'])->name('staff.inventory.alerts.index');
        Route::post('/inventory/alerts/mark-all-read',    [LowStockAlertController::class, 'markAllRead'])->name('staff.inventory.alerts.mark-all-read');
        Route::patch('/inventory/alerts/{lowStockAlert}', [LowStockAlertController::class, 'update'])->name('staff.inventory.alerts.update');
    });

    Route::middleware('permission:Inventory Management,view')->group(function () {
        Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])->name('staff.inventory.show');
    });

    // ── Suppliers ─────────────────────────────────────────────────────────────
    Route::middleware('permission:Inventory Management,view')->group(function () {
        Route::get('/supplier', [SupplierController::class, 'index'])->name('staff.supplier.index');
    });
    Route::middleware('permission:Inventory Management,create')->group(function () {
        Route::get('/supplier/create', [SupplierController::class, 'create'])->name('staff.supplier.create');
        Route::post('/supplier',       [SupplierController::class, 'store'])->name('staff.supplier.store');
    });
    Route::middleware('permission:Inventory Management,update')->group(function () {
        Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'edit'])->name('staff.supplier.edit');
        Route::put('/supplier/{supplier}',      [SupplierController::class, 'update'])->name('staff.supplier.update');
    });
    Route::middleware('permission:Inventory Management,archive')->group(function () {
        Route::get('/supplier/archive',       [SupplierController::class, 'archive'])->name('staff.supplier.archive');
        Route::post('/supplier/{id}/restore', [SupplierController::class, 'restore'])->name('staff.supplier.restore');
        Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('staff.supplier.destroy');
    });

    // ── Activity Logs ─────────────────────────────────────────────────────────
    Route::middleware('permission:HRM,view')->group(function () {
        Route::get('/logs', [ActivityLogsController::class, 'index'])->name('staff.logs.index');
    });

    // ── Attendance ────────────────────────────────────────────────────────────
    Route::middleware('permission:HRM,view')->group(function () {
        Route::get('/attendance',              [AttendanceController::class, 'index'])->name('staff.attendance.index');
    });
    Route::middleware('permission:HRM,update')->group(function () {
        Route::post('/attendance',             [AttendanceController::class, 'store'])->name('staff.attendance.store');
        Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('staff.attendance.update');
    });

    // ── Operations ────────────────────────────────────────────────────────────
    Route::middleware('permission:Operations,view')->group(function () {
        Route::get('/operations/orders',              [ShopOrderController::class, 'index'])->name('staff.orders.index');
        Route::get('/operations/orders/{order}',      [ShopOrderController::class, 'show'])->name('staff.orders.show');
        Route::get('/operations/services',            [ShopServiceController::class, 'index'])->name('staff.services.index');
        Route::get('/operations/promos',              [PromotionController::class, 'index'])->name('staff.promotions.index');
    });
    Route::middleware('permission:Operations,create')->group(function () {
        Route::get('/operations/orders/create',       [ShopOrderController::class, 'create'])->name('staff.orders.create');
        Route::post('/operations/orders',             [ShopOrderController::class, 'store'])->name('staff.orders.store');
        Route::get('/operations/services/create',     [ShopServiceController::class, 'create'])->name('staff.services.create');
        Route::post('/operations/services',           [ShopServiceController::class, 'store'])->name('staff.services.store');
        Route::get('/operations/promos/create',       [PromotionController::class, 'create'])->name('staff.promotions.create');
        Route::post('/operations/promos',             [PromotionController::class, 'store'])->name('staff.promotions.store');
    });
    Route::middleware('permission:Operations,manage')->group(function () {
        Route::get('/operations/orders/{order}/edit',         [ShopOrderController::class, 'edit'])->name('staff.orders.edit');
        Route::put('/operations/orders/{order}',              [ShopOrderController::class, 'update'])->name('staff.orders.update');
        Route::patch('/operations/orders/{order}/restore',    [ShopOrderController::class, 'restore'])->name('staff.orders.restore');
        Route::post('/operations/orders/restore-all',         [ShopOrderController::class, 'restoreAll'])->name('staff.orders.restore-all');
        Route::delete('/operations/orders/{order}',           [ShopOrderController::class, 'destroy'])->name('staff.orders.destroy');
        Route::patch('/operations/orders/{order}/status',     [ShopOrderController::class, 'updateStatus'])->name('staff.orders.status');
        Route::patch('/operations/orders/{order}/payment',    [ShopOrderController::class, 'updatePayment'])->name('staff.orders.payment');
        Route::get('/operations/services/{service}/edit',     [ShopServiceController::class, 'edit'])->name('staff.services.edit');
        Route::put('/operations/services/{service}',          [ShopServiceController::class, 'update'])->name('staff.services.update');
        Route::patch('/operations/services/{service}/toggle', [ShopServiceController::class, 'toggleActive'])->name('staff.services.toggle');
        Route::patch('/operations/services/{service}/restore',[ShopServiceController::class, 'restore'])->name('staff.services.restore');
        Route::post('/operations/services/restore-all',       [ShopServiceController::class, 'restoreAll'])->name('staff.services.restore-all');
        Route::delete('/operations/services/{service}',       [ShopServiceController::class, 'destroy'])->name('staff.services.destroy');
        Route::get('/operations/promos/{promotion}/edit',     [PromotionController::class, 'edit'])->name('staff.promotions.edit');
        Route::put('/operations/promos/{promotion}',          [PromotionController::class, 'update'])->name('staff.promotions.update');
        Route::patch('/operations/promos/{promotion}/toggle', [PromotionController::class, 'toggleActive'])->name('staff.promotions.toggle');
        Route::patch('/operations/promos/{promotion}/restore',[PromotionController::class, 'restore'])->name('staff.promotions.restore');
        Route::delete('/operations/promos/{promotion}',       [PromotionController::class, 'destroy'])->name('staff.promotions.destroy');
    });

    // ── Payroll ───────────────────────────────────────────────────────────────
    Route::middleware('permission:HRM,view')->group(function () {
        Route::get('/payroll',              [PayrollController::class, 'index'])->name('staff.payroll.index');
        Route::get('/payroll/{payroll}',    [PayrollController::class, 'show'])->name('staff.payroll.show');
    });
    Route::middleware('permission:HRM,create')->group(function () {
        Route::post('/payroll',             [PayrollController::class, 'store'])->name('staff.payroll.store');
    });
    Route::middleware('permission:HRM,update')->group(function () {
        Route::put('/payroll/{payrollItem}/item',     [PayrollController::class, 'updateItem'])->name('staff.payroll.item.update');
        Route::post('/payroll/{payroll}/recalculate', [PayrollController::class, 'recalculate'])->name('staff.payroll.recalculate');
        Route::post('/payroll/{payroll}/finalize',    [PayrollController::class, 'finalize'])->name('staff.payroll.finalize');
    });
    Route::middleware('permission:HRM,archive')->group(function () {
        Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('staff.payroll.destroy');
    });

    Route::prefix('reports')->name('staff.reports.')->group(function () {
        Route::middleware('permission:Reports & Analytics,view')->group(function () {
            Route::get('/',          [ReportsController::class, 'overview'])->name('overview');
            Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
            Route::get('/employee',  [ReportsController::class, 'employee'])->name('employee');
        });
    });
});
// ── Normal user routes ─────────────────────────────────────────────────────────

Route::prefix('user')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
