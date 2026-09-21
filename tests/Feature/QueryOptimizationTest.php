<?php

use App\Enums\AccountType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Shop;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;

function createOptimizationShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Query Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'status' => 'active',
    ]);

    return [$owner, $shop];
}

test('employee performance query count remains constant as employees increase', function () {
    [$owner, $shop] = createOptimizationShop();

    foreach (range(1, 12) as $number) {
        $employee = Employee::create([
            'shop_id' => $shop->id,
            'employee_id' => "QUERY-{$number}",
            'first_name' => 'Employee',
            'last_name' => (string) $number,
            'position' => 'Staff',
            'hire_date' => today(),
            'status' => 'Active',
        ]);
        Attendance::create([
            'employee_id' => $employee->id,
            'shop_id' => $shop->id,
            'date' => today(),
            'status' => 'present',
            'marked_by' => $owner->id,
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();
    $performance = app(DashboardService::class)->getEmployeePerformance($shop);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($performance)->toHaveCount(12)
        ->and($queryCount)->toBe(2);
});

test('payroll trend aggregates all payrolls in one query', function () {
    [$owner, $shop] = createOptimizationShop();
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'employee_id' => 'PAYROLL-QUERY',
        'first_name' => 'Payroll',
        'last_name' => 'Employee',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);

    foreach (range(1, 6) as $month) {
        $start = today()->subMonths($month)->startOfMonth();
        $payroll = Payroll::create([
            'shop_id' => $shop->id,
            'period_label' => $start->format('F Y'),
            'period_start' => $start,
            'period_end' => $start->copy()->endOfMonth(),
            'status' => 'finalized',
            'created_by' => $owner->id,
        ]);
        PayrollItem::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'net_pay' => 1000,
            'deductions' => 100,
            'bonuses' => 50,
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();
    $trend = app(DashboardService::class)->getPayrollTrend($shop);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($trend)->toHaveCount(6)
        ->and($queryCount)->toBe(1)
        ->and($trend[0]['net_pay'])->toBe(1000.0);
});

test('admin analytics renders without per-shop growth queries', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    createOptimizationShop();

    $this->actingAs($admin)
        ->get(route('admin.analytics'))
        ->assertOk();
});
