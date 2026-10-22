<?php

// Uses the payShop/payOrder/payStaff helpers declared in OrderPaymentTest.php.

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\PayrollItem;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function urlEmployee($shop, string $code): Employee
{
    return Employee::create([
        'shop_id' => $shop->id, 'employee_id' => $code, 'first_name' => 'Ana', 'last_name' => 'Cruz',
        'position' => 'Staff', 'hire_date' => today(), 'status' => 'Active',
        'user_id' => App\Models\User::factory()->create(['role' => 'staff'])->id,
        'pay_rate' => 500, 'pay_basis' => 'daily',
    ]);
}

function urlPayroll($shop): Payroll
{
    return Payroll::create([
        'shop_id' => $shop->id, 'period_label' => 'October 2026', 'period_start' => '2026-10-01',
        'period_end' => '2026-10-31', 'status' => 'draft', 'created_by' => $shop->owner_id,
    ]);
}

test('shop order pages are reached by ULID and the sequential id is not found', function () {
    $shop = payShop();
    $order = payOrder($shop);

    $this->actingAs($shop->owner)->get("/shop/operations/orders/{$order->payment_public_id}")->assertOk();
    $this->actingAs($shop->owner)->get("/shop/operations/orders/{$order->payment_public_id}/edit")->assertOk();

    $this->actingAs($shop->owner)->get("/shop/operations/orders/{$order->id}")->assertNotFound();
    $this->actingAs($shop->owner)->get("/shop/operations/orders/{$order->id}/edit")->assertNotFound();
    $this->actingAs($shop->owner)
        ->post("/shop/operations/orders/{$order->id}/payments", ['amount' => '10', 'method' => 'cash'])
        ->assertNotFound();
    expect($order->payments()->count())->toBe(0);
});

test('staff order pages also use the ULID', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $staff = payStaff($shop, ['view', 'manage']);

    $this->actingAs($staff)->get("/staff/operations/orders/{$order->payment_public_id}")->assertOk();
    $this->actingAs($staff)->get("/staff/operations/orders/{$order->id}")->assertNotFound();
});

test('payroll is reached by ULID only', function () {
    $shop = payShop();
    $payroll = urlPayroll($shop);

    expect($payroll->public_id)->toHaveLength(26);

    $this->actingAs($shop->owner)->get("/shop/payroll/{$payroll->public_id}")->assertOk();
    $this->actingAs($shop->owner)->get("/shop/payroll/{$payroll->id}")->assertNotFound();
    $this->actingAs($shop->owner)->post("/shop/payroll/{$payroll->id}/finalize")->assertNotFound();
    $this->actingAs($shop->owner)->delete("/shop/payroll/{$payroll->id}")->assertNotFound();
    expect(Payroll::count())->toBe(1);
});

test('payroll items are edited by ULID only', function () {
    $shop = payShop();
    $employee = urlEmployee($shop, 'E-1');
    $item = PayrollItem::create(['payroll_id' => urlPayroll($shop)->id, 'employee_id' => $employee->id, 'net_pay' => 1000]);

    expect($item->public_id)->toHaveLength(26);
    $this->actingAs($shop->owner)->put("/shop/payroll/{$item->id}/item", ['bonuses' => 5])->assertNotFound();
});

test('expenses are changed and restored by ULID only', function () {
    $shop = payShop();
    $category = ExpenseCategory::create(['shop_id' => $shop->id, 'name' => 'Supplies']);
    $expense = Expense::create([
        'shop_id' => $shop->id, 'category_id' => $category->id, 'title' => 'Soap',
        'amount' => 100, 'expense_date' => today(), 'created_by' => $shop->owner_id,
    ]);

    expect($expense->public_id)->toHaveLength(26)->and($category->public_id)->toHaveLength(26);

    $this->actingAs($shop->owner)->delete("/shop/finance/expenses/{$expense->id}")->assertNotFound();
    expect(Expense::count())->toBe(1);

    $this->actingAs($shop->owner)->delete("/shop/finance/expenses/{$expense->public_id}");
    expect(Expense::count())->toBe(0);

    $this->actingAs($shop->owner)->post("/shop/finance/expenses/{$expense->id}/restore")->assertNotFound();
    expect(Expense::count())->toBe(0);

    $this->actingAs($shop->owner)->post("/shop/finance/expenses/{$expense->public_id}/restore");
    expect(Expense::count())->toBe(1);
});

test('another shop cannot delete my expense', function () {
    $mine = payShop('Mine');
    $expense = Expense::create([
        'shop_id' => $mine->id, 'title' => 'Soap', 'amount' => 100, 'expense_date' => today(), 'created_by' => $mine->owner_id,
    ]);

    $other = payShop('Other');
    $response = $this->actingAs($other->owner)->delete("/shop/finance/expenses/{$expense->public_id}");

    expect($response->status())->toBeIn([403, 404])
        ->and(Expense::count())->toBe(1);
});

test('employees are opened and restored by ULID only', function () {
    $shop = payShop();
    $employee = urlEmployee($shop, 'E-2');

    expect($employee->public_id)->toHaveLength(26);

    $this->actingAs($shop->owner)->get("/shop/employee/{$employee->public_id}")->assertOk();
    $this->actingAs($shop->owner)->get("/shop/employee/{$employee->id}")->assertNotFound();
    $this->actingAs($shop->owner)->get("/shop/employee/{$employee->id}/edit")->assertNotFound();

    $employee->delete();
    $this->actingAs($shop->owner)->post("/shop/employee/{$employee->id}/restore")->assertNotFound();
    expect(Employee::count())->toBe(0);

    $this->actingAs($shop->owner)->post("/shop/employee/{$employee->public_id}/restore");
    expect(Employee::count())->toBe(1);
});

test('the archive page gives the restore link the employee ULID', function () {
    $shop = payShop();
    $employee = urlEmployee($shop, 'E-3');
    $this->actingAs($shop->owner);
    $employee->delete();

    $this->actingAs($shop->owner)
        ->get('/shop/employee/archive')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('archived', 1)
            ->where('archived.0.employee_public_id', $employee->public_id));
});

test('a shop cannot open another shops order even with its ULID', function () {
    $mine = payShop('Mine');
    $theirs = payShop('Theirs');
    $order = payOrder($mine);

    $this->actingAs($theirs->owner)->get("/shop/operations/orders/{$order->payment_public_id}")->assertNotFound();
    $this->actingAs($theirs->owner)->get("/shop/operations/orders/{$order->payment_public_id}/edit")->assertNotFound();
});

test('deliveries are changed by ULID only', function () {
    $shop = payShop();
    $order = payOrder($shop, ['status' => 'completed']);
    $delivery = Delivery::create([
        'shop_id' => $shop->id, 'shop_order_id' => $order->id, 'type' => 'delivery',
        'customer_name' => 'X', 'status' => 'pending',
    ]);

    $this->actingAs($shop->owner)->patch("/shop/logistics/{$delivery->id}/status", ['status' => 'cancelled'])->assertNotFound();
    $this->actingAs($shop->owner)->delete("/shop/logistics/{$delivery->id}")->assertNotFound();
    expect(Delivery::count())->toBe(1);
});
