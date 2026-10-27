<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderAutomationSetting;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderStatusAutomationService;
use App\Services\ShopOrderService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/**
 * A shop whose owner has the given subscription: 'paid' (Basic), 'trial', 'expired' or 'none'.
 */
function flowShop(string $subscription = 'paid', string $name = 'Auto Laundry'): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name.' '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);

    if ($subscription !== 'none') {
        Order::create([
            'user_id' => $owner->id,
            'shop_name' => $shop->shop_name,
            'owner_name' => $owner->name,
            'email' => $owner->email,
            'phone' => '09171234567',
            'municipality' => 'Imus',
            'barangay' => 'Poblacion',
            'postal_code' => '4103',
            'plan_name' => $subscription === 'trial' ? 'Standard' : 'Basic',
            'billing_months' => 1,
            'is_trial' => $subscription === 'trial',
            'total_price' => $subscription === 'trial' ? 0 : 3800,
            'status' => $subscription === 'trial' ? 'approved' : 'paid',
            'expires_at' => $subscription === 'expired' ? now()->subDay() : now()->addMonth(),
        ]);
    }

    return $shop;
}

function flowRules(Shop $shop, array $rules = []): ShopOrderAutomationSetting
{
    return ShopOrderAutomationSetting::create(array_merge(
        ['shop_id' => $shop->id],
        ShopOrderAutomationSetting::defaults(),
        $rules,
    ));
}

function flowOrder(Shop $shop, array $overrides = [], ?int $estimatedHours = null, ?User $customer = null): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash '.uniqid(),
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_hours' => $estimatedHours,
    ]);

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'user_id' => $customer?->id,
        'order_source' => $customer ? 'online' : 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => $customer?->name ?? 'Walk In',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'actual_weight_kg' => null,
        'total_amount' => 300,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ], $overrides));
}

function flowCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

/** Records the weight the way the shop's edit form does. */
function flowWeigh(ShopOrder $order, float $kg = 5.0): void
{
    app(ShopOrderService::class)->updateOrder($order, [
        'service_id' => $order->service_id,
        'estimated_weight_kg' => $order->estimated_weight_kg,
        'actual_weight_kg' => $kg,
        'status' => $order->status,
    ]);
}

function flowRun(): array
{
    return app(OrderStatusAutomationService::class)->runScheduled();
}

// ── Plan gate and settings page ──────────────────────────────────────────────

test('only a shop with an active paid plan supports order automation', function (string $subscription, bool $expected) {
    $shop = flowShop($subscription);

    expect(app(App\Services\ShopSetupService::class)->shopSupports($shop->id, 'order-automation'))->toBe($expected);
})->with([
    'paid plan' => ['paid', true],
    'free trial' => ['trial', false],
    'expired plan' => ['expired', false],
    'no plan' => ['none', false],
]);

test('the owner opens the settings page with every rule off and the page unlocked on a paid plan', function () {
    $shop = flowShop();

    $this->actingAs($shop->owner)
        ->get('/shop/operations/order-automation')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/automation/Index')
            ->where('available', true)
            ->where('settings.start_on_weigh_in', false)
            ->where('settings.start_on_payment', false)
            ->where('settings.auto_complete', false)
            ->where('settings.auto_complete_grace_minutes', 120)
            ->where('settings.remind_overdue', false));
});

test('a trial shop sees the page locked', function () {
    $shop = flowShop('trial');

    $this->actingAs($shop->owner)
        ->get('/shop/operations/order-automation')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('available', false));
});

test('the owner saves the rules and the change is logged', function () {
    $shop = flowShop();

    $this->actingAs($shop->owner)
        ->put('/shop/operations/order-automation', [
            'start_on_weigh_in' => true,
            'start_on_payment' => false,
            'auto_complete' => true,
            'auto_complete_grace_minutes' => 45,
            'remind_overdue' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success');

    $saved = ShopOrderAutomationSetting::where('shop_id', $shop->id)->sole();
    expect($saved->start_on_weigh_in)->toBeTrue()
        ->and($saved->start_on_payment)->toBeFalse()
        ->and($saved->auto_complete)->toBeTrue()
        ->and($saved->auto_complete_grace_minutes)->toBe(45)
        ->and($saved->remind_overdue)->toBeTrue();

    $this->assertDatabaseHas('activity_logs', ['shop_id' => $shop->id, 'module' => 'Operations', 'action' => 'Updated order automation settings']);
});

test('saving is refused without a paid plan and nothing is stored', function (string $subscription) {
    $shop = flowShop($subscription);

    $this->actingAs($shop->owner)
        ->put('/shop/operations/order-automation', [
            'start_on_weigh_in' => true,
            'start_on_payment' => true,
            'auto_complete' => true,
            'auto_complete_grace_minutes' => 30,
            'remind_overdue' => true,
        ])
        ->assertForbidden();

    expect(ShopOrderAutomationSetting::count())->toBe(0);
})->with(['trial', 'expired', 'none']);

test('the settings are validated', function (array $payload, string $field) {
    $shop = flowShop();
    $valid = [
        'start_on_weigh_in' => true,
        'start_on_payment' => false,
        'auto_complete' => true,
        'auto_complete_grace_minutes' => 30,
        'remind_overdue' => false,
    ];

    $this->actingAs($shop->owner)
        ->put('/shop/operations/order-automation', array_merge($valid, $payload))
        ->assertSessionHasErrors($field);

    expect(ShopOrderAutomationSetting::count())->toBe(0);
})->with([
    'grace too long' => [['auto_complete_grace_minutes' => 1441], 'auto_complete_grace_minutes'],
    'grace negative' => [['auto_complete_grace_minutes' => -1], 'auto_complete_grace_minutes'],
    'grace not a number' => [['auto_complete_grace_minutes' => 'soon'], 'auto_complete_grace_minutes'],
    'rule not a boolean' => [['auto_complete' => 'maybe'], 'auto_complete'],
]);

test('customers cannot open or save the owner settings', function () {
    $customer = flowCustomer();

    $this->actingAs($customer)->get('/shop/operations/order-automation')->assertRedirect();
    $this->actingAs($customer)->put('/shop/operations/order-automation', [])->assertRedirect();

    expect(ShopOrderAutomationSetting::count())->toBe(0);
});

test('one shop cannot change another shops rules', function () {
    $mine = flowShop('paid', 'Mine');
    $theirs = flowShop('paid', 'Theirs');
    flowRules($theirs, ['auto_complete' => false]);

    $this->actingAs($mine->owner)->put('/shop/operations/order-automation', [
        'start_on_weigh_in' => true,
        'start_on_payment' => true,
        'auto_complete' => true,
        'auto_complete_grace_minutes' => 10,
        'remind_overdue' => true,
    ])->assertRedirect();

    expect(ShopOrderAutomationSetting::where('shop_id', $theirs->id)->sole()->auto_complete)->toBeFalse()
        ->and(ShopOrderAutomationSetting::where('shop_id', $mine->id)->sole()->auto_complete)->toBeTrue();
});

// ── Start on weigh-in ────────────────────────────────────────────────────────

test('confirming the weight starts a pending order and notifies the customer', function () {
    $shop = flowShop();
    flowRules($shop, ['start_on_weigh_in' => true]);
    $customer = flowCustomer();
    $order = flowOrder($shop, [], null, $customer);

    flowWeigh($order);

    $order->refresh();
    expect($order->status)->toBe('in_progress')
        ->and($order->in_progress_at)->not->toBeNull();
    expect($customer->notifications->pluck('data.type'))->toContain('status_changed');

    $log = ActivityLog::where('subject_id', $order->id)->where('shop_id', $shop->id)->latest('id')->first();
    expect($log->action)->toContain('Automatic')
        ->and($log->performed_by)->toBeNull()
        ->and($log->changes['rule'])->toBe('start_on_weigh_in');
});

test('the weight does nothing when the rule is off', function () {
    $shop = flowShop();
    flowRules($shop);
    $order = flowOrder($shop);

    // The optional rule alone: weighing now also starts an order through the built-in start.
    app(OrderStatusAutomationService::class)->onWeightConfirmed($order);

    expect($order->refresh()->status)->toBe('pending');
});

test('the weight does nothing for a shop that has no saved rules', function () {
    $shop = flowShop();
    $order = flowOrder($shop);

    // The optional rule alone: weighing now also starts an order through the built-in start.
    app(OrderStatusAutomationService::class)->onWeightConfirmed($order);

    expect($order->refresh()->status)->toBe('pending');
});

test('the weight rule is ignored once the plan is not paid', function (string $subscription) {
    $shop = flowShop($subscription);
    flowRules($shop, ['start_on_weigh_in' => true]);
    $order = flowOrder($shop);

    // The optional rule alone: weighing now also starts an order through the built-in start.
    app(OrderStatusAutomationService::class)->onWeightConfirmed($order);

    expect($order->refresh()->status)->toBe('pending');
})->with(['trial', 'expired']);

test('re-weighing an order never restarts or reverts it', function (string $status) {
    $shop = flowShop();
    flowRules($shop, ['start_on_weigh_in' => true]);
    $order = flowOrder($shop, ['status' => $status, 'actual_weight_kg' => null]);

    flowWeigh($order);

    expect($order->refresh()->status)->toBe($status);
})->with(['in_progress', 'completed', 'cancelled']);

// ── Start on payment ─────────────────────────────────────────────────────────

test('the first payment starts a pending order', function () {
    $shop = flowShop();
    flowRules($shop, ['start_on_payment' => true]);
    $order = flowOrder($shop, ['actual_weight_kg' => 5]);

    $this->actingAs($shop->owner)
        ->post("/shop/operations/orders/{$order->payment_public_id}/payments", ['amount' => '100', 'method' => 'cash'])
        ->assertSessionHasNoErrors();

    expect($order->refresh()->status)->toBe('in_progress');
});

test('a payment does nothing when the rule is off or the order has moved on', function () {
    $shop = flowShop();
    flowRules($shop, ['start_on_payment' => false]);
    $off = flowOrder($shop);
    $moved = flowOrder($shop, ['status' => 'completed']);

    $this->actingAs($shop->owner);
    $this->post("/shop/operations/orders/{$off->payment_public_id}/payments", ['amount' => '100', 'method' => 'cash']);

    ShopOrderAutomationSetting::where('shop_id', $shop->id)->update(['start_on_payment' => true]);
    $this->post("/shop/operations/orders/{$moved->payment_public_id}/payments", ['amount' => '100', 'method' => 'cash']);

    expect($off->refresh()->status)->toBe('pending')
        ->and($moved->refresh()->status)->toBe('completed');
});

// ── Auto-complete ────────────────────────────────────────────────────────────

test('an overdue order is completed once the grace period has passed', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 120]);
    $customer = flowCustomer();
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHours(3)], null, $customer);

    expect(flowRun())->toMatchArray(['completed' => 1, 'shops' => 1]);

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->completed_at)->not->toBeNull();
    expect($customer->notifications->pluck('data.status'))->toContain('completed');
    expect(ActivityLog::where('subject_id', $order->id)->latest('id')->first()->changes['status'])
        ->toBe(['from' => 'in_progress', 'to' => 'completed']);
});

test('an order is left alone before the due time or inside the grace period', function (int $hoursAgo, int $grace) {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => $grace]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHours($hoursAgo)]);

    flowRun();

    expect($order->refresh()->status)->toBe('in_progress');
})->with([
    'not due yet' => [-2, 0],
    'inside the grace period' => [1, 120],
]);

test('with zero grace an order completes as soon as it is due', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subMinute()]);

    flowRun();

    expect($order->refresh()->status)->toBe('completed');
});

test('without an estimate the due time comes from when work started plus the service hours', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $due = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'in_progress_at' => now()->subHours(5)], 2);
    $notDue = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'in_progress_at' => now()->subHour()], 2);

    flowRun();

    expect($due->refresh()->status)->toBe('completed')
        ->and($notDue->refresh()->status)->toBe('in_progress');
});

test('an order with no estimate and no service hours is never automated', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0, 'remind_overdue' => true]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'created_at' => now()->subDays(30)]);

    flowRun();

    expect($order->refresh()->status)->toBe('in_progress')
        ->and($order->overdue_notified_at)->toBeNull();
});

test('auto-complete only touches shops that switched it on and are on a paid plan', function () {
    $on = flowShop('paid', 'On');
    $off = flowShop('paid', 'Off');
    $trial = flowShop('trial', 'Trial');
    $expired = flowShop('expired', 'Expired');
    flowRules($on, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    flowRules($off, ['auto_complete' => false, 'remind_overdue' => true]);
    flowRules($trial, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    flowRules($expired, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $overdue = ['status' => 'in_progress', 'actual_weight_kg' => 5, 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHours(5)];
    $orders = collect([$on, $off, $trial, $expired])->map(fn ($shop) => flowOrder($shop, $overdue));

    expect(flowRun())->toMatchArray(['completed' => 1]);

    expect($orders->map(fn ($order) => $order->refresh()->status)->all())
        ->toBe(['completed', 'in_progress', 'in_progress', 'in_progress']);
});

test('the scheduler skips cancelled, completed, pending and deleted orders', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $past = ['estimated_completion_at' => now()->subHours(5)];
    $pending = flowOrder($shop, $past + ['status' => 'pending']);
    $cancelled = flowOrder($shop, $past + ['status' => 'cancelled']);
    $trashed = flowOrder($shop, $past + ['status' => 'in_progress', 'actual_weight_kg' => 5]);
    $trashed->delete();

    expect(flowRun())->toMatchArray(['completed' => 0]);

    expect($pending->refresh()->status)->toBe('pending')
        ->and($cancelled->refresh()->status)->toBe('cancelled')
        ->and(ShopOrder::withTrashed()->find($trashed->id)->status)->toBe('in_progress');
});

test('a manual status change is respected and running twice does not repeat the work', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $customer = flowCustomer();
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHour()], null, $customer);

    flowRun();
    expect(flowRun())->toMatchArray(['completed' => 0]);

    expect($customer->notifications->where('data.status', 'completed'))->toHaveCount(1)
        ->and(ActivityLog::where('subject_id', $order->id)->count())->toBe(1);
});

// ── Overdue reminder ─────────────────────────────────────────────────────────

test('the owner is reminded once about an overdue order', function () {
    $shop = flowShop();
    flowRules($shop, ['remind_overdue' => true]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHour()]);

    expect(flowRun())->toMatchArray(['reminded' => 1, 'completed' => 0]);
    expect(flowRun())->toMatchArray(['reminded' => 0]);

    $reminders = $shop->owner->notifications->where('data.type', 'overdue');
    expect($reminders)->toHaveCount(1)
        ->and($reminders->first()->data['url'])->toBe("/shop/operations/orders/{$order->payment_public_id}")
        ->and($order->refresh()->overdue_notified_at)->not->toBeNull()
        ->and($order->status)->toBe('in_progress');
});

test('no reminder is sent before the due time or when the rule is off', function () {
    $shop = flowShop();
    $other = flowShop('paid', 'Other');
    flowRules($shop, ['remind_overdue' => true]);
    flowRules($other, ['remind_overdue' => false, 'auto_complete' => true, 'auto_complete_grace_minutes' => 1440]);
    flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->addHour()]);
    flowOrder($other, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHour()]);

    expect(flowRun())->toMatchArray(['reminded' => 0, 'completed' => 0]);
    expect($shop->owner->notifications)->toHaveCount(0)
        ->and($other->owner->notifications)->toHaveCount(0);
});

// ── Command and bookkeeping ──────────────────────────────────────────────────

test('the orders:automate command runs the scheduled rules', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 5, 'estimated_completion_at' => now()->subHour()]);

    $this->artisan('orders:automate')
        ->expectsOutputToContain('completed 1 order(s)')
        ->assertSuccessful();

    expect($order->refresh()->status)->toBe('completed');
});

test('a manual move to in progress records when work started', function () {
    $shop = flowShop();
    $order = flowOrder($shop);

    app(ShopOrderService::class)->updateStatus($order, 'in_progress');

    expect($order->refresh()->in_progress_at)->not->toBeNull();
});

// ── Review fixes ─────────────────────────────────────────────────────────────

test('an estimate from before work started is stale and does not complete the order early', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    // Estimated for Tuesday, but only started now: the work still needs its 6 hours.
    $late = flowOrder($shop, [
        'status' => 'in_progress',
        'actual_weight_kg' => 5,
        'estimated_completion_at' => now()->subDays(2),
        'in_progress_at' => now()->subMinutes(5),
    ], 6);
    // The same stale estimate, but the service has no hours, so nothing is known: left alone.
    $unknown = flowOrder($shop, [
        'status' => 'in_progress',
        'actual_weight_kg' => 5,
        'estimated_completion_at' => now()->subDays(2),
        'in_progress_at' => now()->subMinutes(5),
    ]);

    expect(flowRun())->toMatchArray(['completed' => 0]);

    expect($late->refresh()->status)->toBe('in_progress')
        ->and($unknown->refresh()->status)->toBe('in_progress');
});

test('an order reopened by hand is not completed again straight away', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $order = flowOrder($shop, [
        'status' => 'completed',
        'actual_weight_kg' => 5,
        'estimated_completion_at' => now()->subDay(),
        'completed_at' => now()->subHours(2),
    ], 4);

    app(ShopOrderService::class)->updateStatus($order, 'in_progress');

    expect(flowRun())->toMatchArray(['completed' => 0]);
    expect($order->refresh()->status)->toBe('in_progress');
});

test('an order without a confirmed weight is never completed automatically', function () {
    $shop = flowShop();
    flowRules($shop, ['auto_complete' => true, 'auto_complete_grace_minutes' => 0]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => null, 'estimated_completion_at' => now()->subHours(3)]);

    expect(flowRun())->toMatchArray(['completed' => 0]);

    expect($order->refresh()->status)->toBe('in_progress');
});

test('setting the same status again changes nothing and notifies nobody', function () {
    $shop = flowShop();
    $customer = flowCustomer();
    $order = flowOrder($shop, ['status' => 'completed', 'completed_at' => now()->subDay()], null, $customer);
    $completedAt = $order->completed_at->toDateTimeString();

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    expect($order->refresh()->completed_at->toDateTimeString())->toBe($completedAt)
        ->and($customer->notifications)->toHaveCount(0);
});

test('an order that goes overdue again after being restarted is reminded about again', function () {
    $shop = flowShop();
    flowRules($shop, ['remind_overdue' => true]);
    $order = flowOrder($shop, ['status' => 'in_progress', 'estimated_completion_at' => now()->subHour()]);

    flowRun();
    expect($order->refresh()->overdue_notified_at)->not->toBeNull();

    app(ShopOrderService::class)->updateStatus($order, 'completed');
    app(ShopOrderService::class)->updateStatus($order->refresh(), 'in_progress');
    expect($order->refresh()->overdue_notified_at)->toBeNull();

    // The restart was a while ago and the new estimate has now passed too.
    $order->update(['in_progress_at' => now()->subHours(2), 'estimated_completion_at' => now()->subMinutes(10)]);
    flowRun();

    expect($shop->owner->notifications->where('data.type', 'overdue'))->toHaveCount(2);
});
