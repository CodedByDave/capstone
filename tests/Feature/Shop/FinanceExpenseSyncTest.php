<?php

use App\Enums\AccountType;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderPaymentService;
use App\Services\PayrollService;
use App\Services\ShopOrderService;

function syncShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Sync Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);
}

function syncOrder(Shop $shop): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Walk In',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'actual_weight_kg' => 5,
        'total_amount' => 300,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'status' => 'in_progress',
    ]);
}

function syncPayroll(Shop $shop): Payroll
{
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'employee_id' => 'EMP-'.uniqid(),
        'first_name' => 'Ana',
        'last_name' => 'Cruz',
        'position' => 'Washer',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    $payroll = Payroll::create([
        'shop_id' => $shop->id,
        'period_label' => 'October 16–31, 2026',
        'period_start' => '2026-10-16',
        'period_end' => '2026-10-31',
        'status' => 'draft',
        'created_by' => $shop->owner_id,
    ]);
    PayrollItem::create([
        'payroll_id' => $payroll->id,
        'employee_id' => $employee->id,
        'basic_salary' => 0,
        'pay_rate' => 550,
        'pay_basis' => 'daily',
        'days_worked' => 10,
        'deductions' => 0,
        'sss_contribution' => 100,
        'philhealth_contribution' => 50,
        'pagibig_contribution' => 50,
        'withholding_tax' => 0,
        'bonuses' => 0,
        'net_pay' => 5300,
    ]);

    return $payroll;
}

test('finalizing a payroll records its cost as a finance expense and deleting it removes it', function () {
    $shop = syncShop();
    $payroll = syncPayroll($shop);
    $this->actingAs($shop->owner);

    app(PayrollService::class)->finalize($payroll);

    $expense = Expense::where('shop_id', $shop->id)->where('source', Expense::SOURCE_PAYROLL)->first();
    expect($expense)->not->toBeNull()
        ->and((float) $expense->amount)->toBe(5500.0)
        ->and($expense->category->name)->toBe('Payroll');

    app(PayrollService::class)->delete($payroll->refresh());

    expect(Expense::where('source', Expense::SOURCE_PAYROLL)->count())->toBe(0);
});

test('cancelling a paid order records a refund and reopening it removes the refund', function () {
    $shop = syncShop();
    $order = syncOrder($shop);
    $this->actingAs($shop->owner);
    app(OrderPaymentService::class)->recordShopPayment($shop->owner, $order, ['amount' => 120, 'method' => 'cash']);

    app(ShopOrderService::class)->updateStatus($order->fresh(), 'cancelled');

    $refund = Expense::where('source', Expense::SOURCE_ORDER_REFUND)->first();
    expect($refund)->not->toBeNull()
        ->and((float) $refund->amount)->toBe(120.0)
        ->and($refund->category->name)->toBe('Customer Refunds');

    app(ShopOrderService::class)->updateStatus($order->fresh(), 'in_progress');

    expect(Expense::where('source', Expense::SOURCE_ORDER_REFUND)->count())->toBe(0);
});

test('cancelling an unpaid order records no refund', function () {
    $shop = syncShop();
    $order = syncOrder($shop);
    $this->actingAs($shop->owner);

    app(ShopOrderService::class)->updateStatus($order, 'cancelled');

    expect(Expense::count())->toBe(0);
});
