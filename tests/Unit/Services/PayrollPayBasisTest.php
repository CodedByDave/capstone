<?php

use App\Models\Employee;
use App\Models\Shop;
use App\Repositories\PayrollRepository;
use App\Services\ActivityLogService;
use App\Services\PayrollService;

it('calculates payroll using the selected pay basis', function (
    string $basis,
    float $rate,
    array $attendance,
    float $expectedNetPay,
    float $expectedMonthlyEquivalent,
) {
    $service = new PayrollService(
        Mockery::mock(PayrollRepository::class),
        Mockery::mock(ActivityLogService::class),
    );

    $employee = new Employee([
        'pay_rate' => $rate,
        'pay_basis' => $basis,
    ]);
    $shop = new Shop([
        'deduct_sss' => false,
        'deduct_philhealth' => false,
        'deduct_pagibig' => false,
        'deduct_withholding_tax' => false,
    ]);

    $method = new ReflectionMethod(PayrollService::class, 'calculatePayrollItem');
    $result = $method->invoke(
        $service,
        $employee,
        $attendance,
        '2026-09-01',
        '2026-09-15',
        $shop,
    );

    expect($result['pay_rate'])->toBe($rate)
        ->and($result['pay_basis'])->toBe($basis)
        ->and($result['basic_salary'])->toBe($expectedMonthlyEquivalent)
        ->and($result['net_pay'])->toBe($expectedNetPay);
})->with([
    'monthly' => ['monthly', 26000.0, ['present' => 1, 'absent' => 0, 'late' => 0, 'half_day' => 0], 1000.0, 26000.0],
    'daily' => ['daily', 800.0, ['present' => 2, 'absent' => 0, 'late' => 0, 'half_day' => 1], 2000.0, 20800.0],
    'hourly' => ['hourly', 100.0, ['present' => 1, 'absent' => 0, 'late' => 0, 'half_day' => 1], 1200.0, 20800.0],
    'per shift' => ['per_shift', 700.0, ['present' => 2, 'absent' => 0, 'late' => 0, 'half_day' => 0], 1400.0, 18200.0],
    'fixed contract' => ['fixed_contract', 15000.0, ['present' => 0, 'absent' => 10, 'late' => 0, 'half_day' => 0], 15000.0, 15000.0],
]);
