<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerAgreementService;
use App\Services\ShopOrderService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function payShop(string $name = 'Pay Laundry'): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $name,
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

/** @param  array<string, mixed>  $overrides */
function payOrder(Shop $shop, array $overrides = []): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash '.uniqid(),
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create(array_merge([
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
    ], $overrides));
}

function payStaff(Shop $shop, array $actions): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'PAY-'.$user->id,
        'first_name' => 'Cash',
        'last_name' => 'Ier',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    $role = 'cashier-'.implode('-', $actions ?: ['none']);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => $role]);

    foreach ($actions as $action) {
        RolePermission::firstOrCreate(['shop_id' => $shop->id, 'role' => $role, 'module' => 'Operations', 'action' => $action]);
    }

    return $user;
}

function payUrl(ShopOrder $order, string $suffix = ''): string
{
    return "/shop/operations/orders/{$order->payment_public_id}/payments{$suffix}";
}

// ─── Recording ────────────────────────────────────────────────────────────────

test('the owner records a partial payment and then the rest', function () {
    $shop = payShop();
    $order = payOrder($shop);

    $this->actingAs($shop->owner)
        ->post(payUrl($order), ['amount' => '100.50', 'method' => 'cash', 'reference' => 'R-1', 'note' => 'Deposit'])
        ->assertSessionHasNoErrors();

    $order->refresh();
    expect((float) $order->amount_paid)->toBe(100.5)
        ->and($order->payment_status)->toBe('partial')
        ->and($order->paid_at)->toBeNull()
        ->and($order->balanceDue())->toBe(199.5);

    $payment = $order->payments()->first();
    expect($payment->status)->toBe('confirmed')
        ->and($payment->source)->toBe('shop')
        ->and($payment->recorded_by)->toBe($shop->owner_id)
        ->and($payment->shop_id)->toBe($shop->id)
        ->and($payment->reference)->toBe('R-1')
        ->and($payment->public_id)->toHaveLength(26);

    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '199.50', 'method' => 'gcash']);

    $order->refresh();
    expect($order->payment_status)->toBe('paid')
        ->and((float) $order->amount_paid)->toBe(300.0)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->payments()->count())->toBe(2)
        ->and($order->payment_method)->toBe('gcash');
});

test('recording a payment notifies a linked customer with the amount and remaining balance', function () {
    $shop = payShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = payOrder($shop, ['user_id' => $customer->id]);

    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '120', 'method' => 'cash']);

    $notification = $customer->notifications()->first();
    expect($notification->data['type'])->toBe('payment_updated')
        ->and($notification->data['title'])->toBe('Payment received')
        ->and($notification->data['body'])->toContain('₱120.00')->toContain('₱180.00');

    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '180', 'method' => 'cash']);

    expect($customer->notifications()->latest('created_at')->get()->pluck('data.title'))->toContain('Order fully paid');
});

test('invalid payments are rejected', function (array $input, string $field) {
    $shop = payShop();
    $order = payOrder($shop);

    $this->actingAs($shop->owner)
        ->post(payUrl($order), $input)
        ->assertSessionHasErrors($field);

    expect($order->payments()->count())->toBe(0)
        ->and($order->fresh()->payment_status)->toBe('unpaid');
})->with([
    'zero' => [['amount' => '0', 'method' => 'cash'], 'amount'],
    'negative' => [['amount' => '-5', 'method' => 'cash'], 'amount'],
    'three decimals' => [['amount' => '10.123', 'method' => 'cash'], 'amount'],
    'not a number' => [['amount' => 'abc', 'method' => 'cash'], 'amount'],
    'missing amount' => [['method' => 'cash'], 'amount'],
    'missing method' => [['amount' => '10'], 'method'],
    'unknown method' => [['amount' => '10', 'method' => 'bitcoin'], 'method'],
    'over the balance' => [['amount' => '300.01', 'method' => 'cash'], 'amount'],
]);

test('a payment cannot be recorded on an order that is already paid in full', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '300', 'method' => 'cash']);

    $this->actingAs($shop->owner)
        ->post(payUrl($order), ['amount' => '1', 'method' => 'cash'])
        ->assertSessionHasErrors('amount');

    expect($order->payments()->count())->toBe(1);
});

test('a cancelled order cannot receive payments', function () {
    $shop = payShop();
    $order = payOrder($shop, ['status' => 'cancelled']);

    $this->actingAs($shop->owner)
        ->post(payUrl($order), ['amount' => '50', 'method' => 'cash'])
        ->assertSessionHasErrors('amount');

    expect($order->payments()->count())->toBe(0);
});

// ─── Voiding ──────────────────────────────────────────────────────────────────

test('a mistaken payment is voided with a reason, kept in the history and the balance comes back', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '300', 'method' => 'cash']);
    $payment = $order->payments()->first();

    $this->actingAs($shop->owner)
        ->post(payUrl($order, "/{$payment->public_id}/void"), [])
        ->assertSessionHasErrors('reason');
    expect($order->fresh()->payment_status)->toBe('paid');

    $this->actingAs($shop->owner)
        ->post(payUrl($order, "/{$payment->public_id}/void"), ['reason' => 'Typed the wrong order'])
        ->assertSessionHasNoErrors();

    $order->refresh();
    $payment->refresh();
    expect($order->payment_status)->toBe('unpaid')
        ->and((float) $order->amount_paid)->toBe(0.0)
        ->and($order->paid_at)->toBeNull()
        ->and($payment->status)->toBe('voided')
        ->and($payment->void_reason)->toBe('Typed the wrong order')
        ->and($payment->voided_by)->toBe($shop->owner_id)
        ->and($payment->voided_at)->not->toBeNull();

    // A voided payment cannot be voided twice.
    $this->actingAs($shop->owner)
        ->post(payUrl($order, "/{$payment->public_id}/void"), ['reason' => 'Again'])
        ->assertSessionHasErrors('reason');
});

test('voiding one of two payments leaves the order partially paid', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '100', 'method' => 'cash']);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '200', 'method' => 'gcash']);
    $second = $order->payments()->where('amount', 200)->first();

    $this->actingAs($shop->owner)->post(payUrl($order, "/{$second->public_id}/void"), ['reason' => 'Bounced']);

    expect($order->fresh()->payment_status)->toBe('partial')
        ->and((float) $order->fresh()->amount_paid)->toBe(100.0);
});

// ─── Permissions and shop isolation ───────────────────────────────────────────

test('staff with Operations manage can record payments but view-only staff cannot', function () {
    $shop = payShop();
    $order = payOrder($shop);

    $viewer = payStaff($shop, ['view']);
    $response = $this->actingAs($viewer)->post("/staff/operations/orders/{$order->payment_public_id}/payments", ['amount' => '50', 'method' => 'cash']);
    expect($response->status())->toBeIn([302, 403])
        ->and($order->payments()->count())->toBe(0);

    $manager = payStaff($shop, ['view', 'manage']);
    $this->actingAs($manager)
        ->post("/staff/operations/orders/{$order->payment_public_id}/payments", ['amount' => '50', 'method' => 'cash'])
        ->assertSessionHasNoErrors();

    expect($order->payments()->count())->toBe(1)
        ->and($order->payments()->first()->recorded_by)->toBe($manager->id);
});

test('another shop cannot record, void or check payments on my order', function () {
    $mine = payShop('Mine');
    $theirs = payShop('Theirs');
    $order = payOrder($mine);
    $this->actingAs($mine->owner)->post(payUrl($order), ['amount' => '100', 'method' => 'cash']);
    $payment = $order->payments()->first();

    $this->actingAs($theirs->owner)->post(payUrl($order), ['amount' => '50', 'method' => 'cash'])->assertNotFound();
    $this->actingAs($theirs->owner)->post(payUrl($order, "/{$payment->public_id}/void"), ['reason' => 'Sabotage'])->assertNotFound();
    $this->actingAs($theirs->owner)->post(payUrl($order, '/check'))->assertNotFound();

    expect($order->payments()->count())->toBe(1)
        ->and($payment->fresh()->status)->toBe('confirmed');
});

test('a payment of another order cannot be voided through my order', function () {
    $shop = payShop();
    $first = payOrder($shop);
    $second = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($second), ['amount' => '100', 'method' => 'cash']);
    $payment = $second->payments()->first();

    $this->actingAs($shop->owner)
        ->post(payUrl($first, "/{$payment->public_id}/void"), ['reason' => 'Wrong order'])
        ->assertNotFound();

    expect($payment->fresh()->status)->toBe('confirmed');
});

test('the order page lists its payments', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '100', 'method' => 'cash']);

    $this->actingAs($shop->owner)
        ->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/orders/Show')
            ->has('payments', 1)
            ->where('payments.0.amount', 100)
            ->where('payments.0.status', 'confirmed')
            ->missing('payments.0.paymongo_session_id'));
});

// ─── Creating an order with money received ────────────────────────────────────

function createOrderWithPayment(Shop $shop, float $received): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Create '.uniqid(),
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return app(ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'customer_name' => 'Walk In',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'total_amount' => 300,
        'payment_method' => 'gcash',
        'payment_status' => 'paid', // a forged status must be ignored
        'amount_paid' => $received,
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
    ], $shop->owner);
}

test('money received while creating an order becomes a recorded payment', function () {
    $shop = payShop();

    $order = createOrderWithPayment($shop, 100);

    expect($order->payments)->toHaveCount(1)
        ->and((float) $order->payments->first()->amount)->toBe(100.0)
        ->and($order->payments->first()->method)->toBe('gcash')
        ->and($order->fresh()->payment_status)->toBe('partial')
        ->and((float) $order->fresh()->amount_paid)->toBe(100.0);
});

test('creating an order with no money received records no payment and ignores a forged status', function () {
    $shop = payShop();

    $order = createOrderWithPayment($shop, 0);

    expect($order->payments)->toHaveCount(0)
        ->and($order->fresh()->payment_status)->toBe('unpaid')
        ->and($order->fresh()->paid_at)->toBeNull();
});

test('more money than the order total cannot be received while creating an order', function () {
    $shop = payShop();

    expect(fn () => createOrderWithPayment($shop, 300.01))->toThrow(Illuminate\Validation\ValidationException::class);
    expect(ShopOrder::count())->toBe(0);
});

test('editing an order recalculates the status from the recorded payments', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '300', 'method' => 'cash']);
    expect($order->fresh()->payment_status)->toBe('paid');

    // The new total is higher, so what was paid no longer covers it. The form cannot override this.
    $order->update(['total_amount' => 400]);
    app(Illuminate\Contracts\Container\Container::class)->make(App\Services\OrderPaymentService::class)
        ->sync($order->fresh());

    expect($order->fresh()->payment_status)->toBe('partial')
        ->and($order->fresh()->paid_at)->toBeNull();
});

// ─── Online payments in parts ─────────────────────────────────────────────────

function onlinePayOrder(array $orderOverrides = []): array
{
    config()->set('services.paymongo.secret_key', 'sk_test_platform');
    config()->set('services.paymongo.demo_enabled', true);
    config()->set('services.paymongo.demo_secret_key', 'sk_test_demoLaundryHubPanel2026');
    config()->set('services.paymongo.demo_public_key', 'pk_test_demoLaundryHubPanel2026');

    $shop = payShop('Online Laundry');
    $shop->update([
        'paymongo_secret_key' => 'sk_test_demoLaundryHubPanel2026',
        'paymongo_public_key' => 'pk_test_demoLaundryHubPanel2026',
    ]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = payOrder($shop, array_merge([
        'user_id' => $customer->id,
        'order_source' => 'online',
        'customer_phone' => '09171234567',
    ], $orderOverrides));

    return compact('shop', 'customer', 'order');
}

function fakeCheckout(string $sessionId = 'cs_part_1', string $status = 'active', string $paymentStatus = 'awaiting_payment_method'): void
{
    // Start from a clean fake so a later call replaces the earlier session.
    Http::swap(new Illuminate\Http\Client\Factory);
    Http::fake([
        'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
            'data' => ['id' => $sessionId, 'attributes' => ['checkout_url' => "https://checkout.paymongo.test/{$sessionId}"]],
        ]),
        "https://api.paymongo.com/v1/checkout_sessions/{$sessionId}" => Http::response([
            'data' => ['id' => $sessionId, 'attributes' => [
                'status' => $status,
                'payment_intent' => ['attributes' => [
                    'status' => $paymentStatus,
                    'payments' => [['id' => 'pay_'.$sessionId]],
                ]],
            ]],
        ]),
    ]);
}

function payOnline($test, User $customer, ShopOrder $order, array $input)
{
    $response = $test->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.pay-online', $order->payment_public_id), $input);
    $test->flushHeaders();

    return $response;
}

test('a customer can pay part of the balance online', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();

    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '150'])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.paymongo.test/cs_part_1');

    $pending = $order->payments()->first();
    expect($pending->status)->toBe('pending')
        ->and($pending->source)->toBe('online')
        ->and((float) $pending->amount)->toBe(150.0)
        ->and($pending->paymongo_session_id)->toBe('cs_part_1')
        ->and($order->fresh()->payment_status)->toBe('unpaid');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
        && data_get($request->data(), 'data.attributes.line_items.0.amount') === 15000
        && str_contains(data_get($request->data(), 'data.attributes.line_items.0.name'), 'partial'));
});

test('without an amount the whole remaining balance is charged', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();

    payOnline($this, $customer, $order, ['payment_method' => 'gcash']);

    expect((float) $order->payments()->first()->amount)->toBe(300.0);
    Http::assertSent(fn ($request) => data_get($request->data(), 'data.attributes.line_items.0.amount') === 30000);
});

test('an online partial payment must be at least 100 and no more than the balance', function (string $amount) {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();

    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => $amount]);

    expect($order->payments()->count())->toBe(0);
    Http::assertNothingSent();
})->with(['99.99', '50', '0', '-100', '300.01', '1000']);

test('returning from checkout confirms the partial payment once and leaves a balance', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '120']);

    $this->actingAs($customer)
        ->get(route('user.orders.payment.success', $order->payment_public_id))
        ->assertRedirect(route('user.orders.payment.receipt', $order->payment_public_id));

    // Opening the success page again must not count the payment a second time.
    $this->actingAs($customer)->get(route('user.orders.payment.success', $order->payment_public_id));

    $order->refresh();
    expect((float) $order->amount_paid)->toBe(120.0)
        ->and($order->payment_status)->toBe('partial')
        ->and($order->paymongo_payment_id)->toBe('pay_cs_part_1')
        ->and($order->payments()->confirmed()->count())->toBe(1)
        ->and($customer->notifications()->count())->toBe(1);

    $this->actingAs($customer)
        ->get(route('user.orders.payment.receipt', $order->payment_public_id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user/payment/Receipt')
            ->where('receipt.amount_paid', 120)
            ->where('receipt.balance_due', 180)
            ->where('receipt.is_fully_paid', false));
});

test('paying the rest online completes the order after a partial payment', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    $order->payments()->create([
        'shop_id' => $order->shop_id, 'amount' => 100, 'method' => 'cash',
        'status' => 'confirmed', 'source' => 'shop', 'paid_at' => now(),
    ]);
    $order->update(['amount_paid' => 100, 'payment_status' => 'partial']);

    payOnline($this, $customer, $order, ['payment_method' => 'gcash']);
    expect((float) $order->payments()->where('status', 'pending')->first()->amount)->toBe(200.0);

    $this->actingAs($customer)->get(route('user.orders.payment.success', $order->payment_public_id));

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and((float) $order->fresh()->amount_paid)->toBe(300.0);
});

test('starting a new checkout cancels the earlier unfinished one', function () {
    fakeCheckout('cs_first');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    fakeCheckout('cs_second');
    payOnline($this, $customer, $order, ['payment_method' => 'maya', 'amount' => '200']);

    expect($order->payments()->where('status', 'pending')->count())->toBe(1)
        ->and($order->payments()->where('status', 'cancelled')->count())->toBe(1)
        ->and($order->payments()->where('status', 'pending')->first()->paymongo_session_id)->toBe('cs_second');
});

test('a customer who already paid in another tab is not charged again', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash']);

    // The first checkout was actually paid; starting another one must notice that first.
    payOnline($this, $customer, $order, ['payment_method' => 'gcash']);

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and($order->payments()->confirmed()->count())->toBe(1)
        ->and($order->payments()->where('status', 'pending')->count())->toBe(0);
});

test('backing out of checkout cancels the pending payment', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->actingAs($customer)
        ->get(route('user.orders.payment.cancel', $order->payment_public_id))
        ->assertRedirect(route('user.orders.show', $order->payment_public_id));

    expect($order->payments()->first()->status)->toBe('cancelled')
        ->and($order->fresh()->payment_status)->toBe('unpaid');
});

test('another customer cannot pay, check or cancel my order', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    $intruder = User::factory()->create(['role' => AccountType::Customer->value]);

    payOnline($this, $intruder, $order, ['payment_method' => 'gcash', 'amount' => '100'])->assertNotFound();
    $this->actingAs($intruder)->post(route('user.orders.payment.check', $order->payment_public_id))->assertNotFound();
    $this->actingAs($intruder)->get(route('user.orders.payment.cancel', $order->payment_public_id))->assertNotFound();

    expect($order->payments()->count())->toBe(0);
});

test('the order page shows a customer their payments and the pending one', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    $order->payments()->create([
        'shop_id' => $order->shop_id, 'amount' => 100, 'method' => 'cash',
        'status' => 'confirmed', 'source' => 'shop', 'paid_at' => now(),
    ]);
    $order->update(['amount_paid' => 100, 'payment_status' => 'partial']);
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user/OrderDetail')
            ->where('order.balance_due', 200)
            ->has('order.payments', 1)
            ->where('order.payments.0.amount', 100)
            ->where('order.pending.amount', 100));
});

// ─── Checking the status of an online payment ─────────────────────────────────

test('the customer can check a payment that went through without them returning', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->actingAs($customer)
        ->post(route('user.orders.payment.check', $order->payment_public_id))
        ->assertSessionHas('toast.type', 'success');

    expect($order->fresh()->payment_status)->toBe('partial')
        ->and((float) $order->fresh()->amount_paid)->toBe(100.0);
});

test('checking an unfinished payment leaves it pending', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->actingAs($customer)
        ->post(route('user.orders.payment.check', $order->payment_public_id))
        ->assertSessionHas('toast.type', 'warning');

    expect($order->payments()->first()->status)->toBe('pending');
});

test('the shop can check the online payment of its own order', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['shop' => $shop, 'customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->actingAs($shop->owner)
        ->post(payUrl($order, '/check'))
        ->assertSessionHas('toast.message', 'The online payment was confirmed.');

    expect($order->fresh()->payment_status)->toBe('partial')
        ->and($order->payments()->first()->status)->toBe('confirmed');
});

// ─── Scheduled reconciliation ─────────────────────────────────────────────────

function agePending(ShopOrder $order, $age): void
{
    $order->payments()->update(['created_at' => $age]);
}

test('the reconcile command confirms a paid checkout exactly once', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);
    agePending($order, now()->subMinutes(10));

    $this->artisan('payments:reconcile')->assertSuccessful();
    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($order->fresh()->payment_status)->toBe('partial')
        ->and((float) $order->fresh()->amount_paid)->toBe(100.0)
        ->and($order->payments()->confirmed()->count())->toBe(1)
        ->and($customer->notifications()->count())->toBe(1);
});

test('the reconcile command leaves an unpaid checkout pending', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);
    agePending($order, now()->subMinutes(10));

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($order->payments()->first()->status)->toBe('pending')
        ->and($order->fresh()->payment_status)->toBe('unpaid');
});

test('the reconcile command does not touch a checkout that was only just started', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($order->payments()->first()->status)->toBe('pending');
});

test('the reconcile command gives up on checkouts older than a day', function () {
    fakeCheckout();
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);
    agePending($order, now()->subHours(25));

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($order->payments()->first()->status)->toBe('cancelled')
        ->and($order->fresh()->payment_status)->toBe('unpaid');
});

test('one failing checkout does not stop the others from being reconciled', function () {
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();
    $other = onlinePayOrder();

    Http::fake([
        'https://api.paymongo.com/v1/checkout_sessions' => Http::sequence()
            ->push(['data' => ['id' => 'cs_bad', 'attributes' => ['checkout_url' => 'https://x.test/bad']]])
            ->push(['data' => ['id' => 'cs_good', 'attributes' => ['checkout_url' => 'https://x.test/good']]]),
        'https://api.paymongo.com/v1/checkout_sessions/cs_bad' => Http::response(['errors' => []], 500),
        'https://api.paymongo.com/v1/checkout_sessions/cs_good' => Http::response([
            'data' => ['id' => 'cs_good', 'attributes' => ['status' => 'paid', 'payment_intent' => ['attributes' => [
                'status' => 'succeeded', 'payments' => [['id' => 'pay_good']],
            ]]]],
        ]),
    ]);

    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);
    payOnline($this, $other['customer'], $other['order'], ['payment_method' => 'gcash', 'amount' => '100']);
    ShopOrderPayment::query()->update(['created_at' => now()->subMinutes(10)]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($order->payments()->first()->status)->toBe('pending')
        ->and($other['order']->payments()->first()->status)->toBe('confirmed');
});

// ─── Income reporting ─────────────────────────────────────────────────────────

test('finance income counts each payment on the day it was received', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '100', 'method' => 'cash']);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '200', 'method' => 'gcash']);
    // The first part was actually received in an earlier month.
    $order->payments()->where('amount', 100)->update(['paid_at' => now()->subMonths(2)]);

    $other = payShop('Other');
    $otherOrder = payOrder($other);
    $this->actingAs($other->owner)->post(payUrl($otherOrder), ['amount' => '250', 'method' => 'cash']);

    $this->actingAs($shop->owner)
        ->get('/shop/finance/income')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/finance/Income')
            ->where('stats.total_income', 300)
            ->where('stats.this_month_income', 200)
            ->where('stats.total_count', 1)
            ->has('income.data', 2)
            ->where('income.data.0.amount_paid', '200.00')
            ->where('income.data.0.payment_method', 'gcash')
            ->where('income.data.0.order_number', $order->order_number));
});

test('finance income leaves out voided payments', function () {
    $shop = payShop();
    $order = payOrder($shop);
    $this->actingAs($shop->owner)->post(payUrl($order), ['amount' => '100', 'method' => 'cash']);
    $payment = $order->payments()->first();
    $this->actingAs($shop->owner)->post(payUrl($order, "/{$payment->public_id}/void"), ['reason' => 'Mistake']);

    $this->actingAs($shop->owner)
        ->get('/shop/finance/income')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('stats.total_income', 0)->has('income.data', 0));
});

// ─── Backfill ─────────────────────────────────────────────────────────────────

test('the migration gives orders that were already paid one confirmed payment each', function () {
    $shop = payShop();
    $paid = payOrder($shop, ['amount_paid' => 300, 'payment_status' => 'paid', 'paid_at' => now()->subDay(), 'payment_method' => 'gcash']);
    $partial = payOrder($shop, ['amount_paid' => 80, 'payment_status' => 'partial', 'payment_method' => 'online']);
    $none = payOrder($shop);

    // Run the real migration against a table created without it having run on these rows.
    Illuminate\Support\Facades\Schema::drop('shop_order_payments');
    $migration = require database_path('migrations/2026_10_25_000001_create_shop_order_payments_table.php');
    $migration->up();

    expect($paid->payments()->count())->toBe(1)
        ->and($paid->payments()->first()->method)->toBe('gcash')
        ->and((float) $paid->payments()->first()->amount)->toBe(300.0)
        ->and($paid->payments()->first()->status)->toBe('confirmed')
        ->and($partial->payments()->first()->method)->toBe('online')
        ->and((float) $partial->payments()->first()->amount)->toBe(80.0)
        ->and($none->payments()->count())->toBe(0);
});

test('a checkout that was replaced by a newer one is still credited if the customer paid it', function () {
    ['customer' => $customer, 'order' => $order] = onlinePayOrder();

    fakeCheckout('cs_old');
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);
    fakeCheckout('cs_new');
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);
    expect($order->payments()->where('status', 'cancelled')->count())->toBe(1);

    // PayMongo later reports the replaced checkout as paid.
    Http::swap(new Illuminate\Http\Client\Factory);
    Http::fake([
        'https://api.paymongo.com/v1/checkout_sessions/cs_old' => Http::response([
            'data' => ['id' => 'cs_old', 'attributes' => ['status' => 'paid', 'payment_intent' => ['attributes' => [
                'status' => 'succeeded', 'payments' => [['id' => 'pay_old']],
            ]]]],
        ]),
        'https://api.paymongo.com/v1/checkout_sessions/cs_new' => Http::response([
            'data' => ['id' => 'cs_new', 'attributes' => ['status' => 'active']],
        ]),
    ]);
    ShopOrderPayment::query()->update(['created_at' => now()->subMinutes(10)]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect((float) $order->fresh()->amount_paid)->toBe(100.0)
        ->and($order->payments()->confirmed()->count())->toBe(1)
        ->and($order->payments()->confirmed()->first()->paymongo_session_id)->toBe('cs_old');
});

test('cancelling an order checks for an online payment that was already made', function () {
    fakeCheckout(status: 'paid', paymentStatus: 'succeeded');
    ['customer' => $customer, 'order' => $order] = onlinePayOrder(['status' => 'pending']);
    payOnline($this, $customer, $order, ['payment_method' => 'gcash', 'amount' => '100']);

    $this->actingAs($customer)->post(route('user.orders.cancel', $order->payment_public_id));

    expect($order->payments()->confirmed()->count())->toBe(1)
        ->and((float) $order->fresh()->amount_paid)->toBe(100.0);
});

test('the migration backfills a legacy paid order that has no amount recorded', function () {
    $shop = payShop();
    $legacy = payOrder($shop, ['amount_paid' => 0, 'payment_status' => 'paid']);

    Illuminate\Support\Facades\Schema::drop('shop_order_payments');
    (require database_path('migrations/2026_10_25_000001_create_shop_order_payments_table.php'))->up();

    expect((float) $legacy->payments()->first()->amount)->toBe(300.0);
});
