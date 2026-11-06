<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Services\ShopOrderService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A set-up shop on a paid plan, with a rush fee unless told otherwise. */
function queueShop(string $name = 'Queue Laundry', float $rushFee = 50): Shop
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
        'rush_fee' => $rushFee,
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
function queueOrder(Shop $shop, array $overrides = []): ShopOrder
{
    $createdAt = $overrides['created_at'] ?? null;
    unset($overrides['created_at']);

    $service = ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash and Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    $order = ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Customer '.uniqid(),
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'unpaid',
        'status' => 'pending',
    ], $overrides));

    if ($createdAt !== null) {
        $order->forceFill(['created_at' => $createdAt])->saveQuietly();
    }

    return $order;
}

function queueStaff(Shop $shop, array $actions): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'Q-'.$user->id,
        'first_name' => 'Queue',
        'last_name' => 'Staff',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    $role = 'queue-'.implode('-', $actions ?: ['none']);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => $role]);

    foreach ($actions as $action) {
        RolePermission::firstOrCreate(['shop_id' => $shop->id, 'role' => $role, 'module' => 'Operations', 'action' => $action]);
    }

    return $user;
}

function rushUrl(ShopOrder $order, string $base = '/shop'): string
{
    return "{$base}/operations/orders/{$order->payment_public_id}/rush";
}

/** The page's rows as order number => queue position. */
function queuePositions($response): array
{
    return collect($response->viewData('page')['props']['orders']['data'])
        ->mapWithKeys(fn ($row) => [$row['order_number'] => $row['queue_position'] ?? null])
        ->all();
}

// ─── Queue positions ──────────────────────────────────────────────────────────

test('waiting orders are numbered first come, first served', function () {
    $shop = queueShop();
    queueOrder($shop, ['order_number' => 'SECOND', 'created_at' => now()->subHours(2)]);
    queueOrder($shop, ['order_number' => 'FIRST', 'created_at' => now()->subHours(5)]);
    queueOrder($shop, ['order_number' => 'THIRD', 'created_at' => now()->subHour()]);

    $response = $this->actingAs($shop->owner)->get('/shop/operations/orders')->assertOk();

    expect(queuePositions($response))->toBe(['THIRD' => 3, 'SECOND' => 2, 'FIRST' => 1]);
});

test('a rush order takes the first place and the others move back', function () {
    $shop = queueShop();
    queueOrder($shop, ['order_number' => 'OLDEST', 'created_at' => now()->subHours(5)]);
    queueOrder($shop, ['order_number' => 'RUSHED', 'is_rush' => true, 'rush_fee' => 50, 'created_at' => now()->subHour()]);

    $response = $this->actingAs($shop->owner)->get('/shop/operations/orders')->assertOk();

    expect(queuePositions($response))->toBe(['RUSHED' => 1, 'OLDEST' => 2]);
});

test('orders still to be worked on are in the queue, finished ones are not, and each shop has its own', function () {
    $shop = queueShop();
    $other = queueShop('Other Laundry');
    queueOrder($shop, ['order_number' => 'DONE', 'status' => 'completed', 'created_at' => now()->subHours(9)]);
    queueOrder($shop, ['order_number' => 'WORKING', 'status' => 'in_progress', 'created_at' => now()->subHours(4)]);
    queueOrder($shop, ['order_number' => 'WAITING', 'created_at' => now()->subHours(3)]);
    queueOrder($other, ['order_number' => 'THEIRS-1', 'created_at' => now()->subHours(6)]);
    queueOrder($other, ['order_number' => 'THEIRS-2', 'created_at' => now()->subHours(5)]);

    $response = $this->actingAs($shop->owner)->get('/shop/operations/orders')->assertOk();

    expect(queuePositions($response))->toBe(['WAITING' => 2, 'WORKING' => 1, 'DONE' => null]);
});

test('the queue filter lists only the orders still to be worked on', function () {
    $shop = queueShop();
    queueOrder($shop, ['order_number' => 'WAITING']);
    queueOrder($shop, ['order_number' => 'WORKING', 'status' => 'in_progress']);
    queueOrder($shop, ['order_number' => 'DONE', 'status' => 'completed']);
    queueOrder($shop, ['order_number' => 'GONE', 'status' => 'cancelled']);

    $response = $this->actingAs($shop->owner)->get('/shop/operations/orders?status=queue')->assertOk();

    expect(array_keys(queuePositions($response)))->toEqualCanonicalizing(['WAITING', 'WORKING']);
});

test('the queue sort lists waiting orders in the order they should be worked on', function () {
    $shop = queueShop();
    queueOrder($shop, ['order_number' => 'B', 'created_at' => now()->subHours(3)]);
    queueOrder($shop, ['order_number' => 'A', 'created_at' => now()->subHours(4)]);
    queueOrder($shop, ['order_number' => 'RUSH', 'is_rush' => true, 'rush_fee' => 50, 'created_at' => now()->subHour()]);
    queueOrder($shop, ['order_number' => 'DONE', 'status' => 'completed', 'created_at' => now()->subHours(9)]);

    $response = $this->actingAs($shop->owner)
        ->get('/shop/operations/orders?sort_by=queue&sort_direction=asc')
        ->assertOk();

    expect(array_keys(queuePositions($response)))->toBe(['RUSH', 'A', 'B', 'DONE']);
});

test('the orders page reports how many orders are in the queue and how many are rush', function () {
    $shop = queueShop();
    queueOrder($shop);
    queueOrder($shop, ['is_rush' => true, 'rush_fee' => 50]);
    queueOrder($shop, ['status' => 'in_progress', 'is_rush' => true, 'rush_fee' => 50]);
    queueOrder($shop, ['status' => 'completed', 'is_rush' => true, 'rush_fee' => 50]);

    $this->actingAs($shop->owner)->get('/shop/operations/orders')
        ->assertInertia(fn ($page) => $page
            ->where('queueSummary.waiting', 3)
            ->where('queueSummary.rush', 2)
            ->where('rushFee', 50));
});

// ─── Rushing an order ─────────────────────────────────────────────────────────

test('the owner rushes a waiting order and the fee is added to the total', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop);

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true])->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->is_rush)->toBeTrue()
        ->and((float) $order->rush_fee)->toBe(50.0)
        ->and((float) $order->total_amount)->toBe(230.0)
        ->and($order->rush_marked_at)->not->toBeNull()
        ->and(ActivityLog::where('subject_id', $order->id)->where('action', 'Marked order as rush')->exists())->toBeTrue();
});

test('the rush fee is taxed with the rest of the order', function () {
    $shop = queueShop(rushFee: 100);
    $order = queueOrder($shop, ['tax_rate' => 12, 'tax_amount' => 21.6, 'total_amount' => 201.6]);

    app(ShopOrderService::class)->setRush($order, true, $shop->owner);

    // 180 + 100 = 280 taxable, 12% = 33.60
    expect((float) $order->fresh()->total_amount)->toBe(313.6)
        ->and((float) $order->fresh()->tax_amount)->toBe(33.6);
});

test('rushing an order jumps it to the front of the queue', function () {
    $shop = queueShop();
    queueOrder($shop, ['order_number' => 'FIRST', 'created_at' => now()->subHours(5)]);
    $late = queueOrder($shop, ['order_number' => 'LATE', 'created_at' => now()->subHour()]);

    $this->actingAs($shop->owner)->patch(rushUrl($late), ['rush' => true]);
    $response = $this->actingAs($shop->owner)->get('/shop/operations/orders')->assertOk();

    expect(queuePositions($response))->toBe(['LATE' => 1, 'FIRST' => 2]);
});

test('rushing an order that is already paid leaves a balance to collect', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop);
    $this->actingAs($shop->owner)->post("/shop/operations/orders/{$order->payment_public_id}/payments", [
        'amount' => '180',
        'method' => 'cash',
    ])->assertSessionHasNoErrors();
    expect($order->fresh()->payment_status)->toBe('paid');

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true])->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->payment_status)->toBe('partial')
        ->and($order->balanceDue())->toBe(50.0);
});

test('rushing again changes nothing', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop);

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true]);
    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true])->assertSessionHasNoErrors();

    expect((float) $order->fresh()->total_amount)->toBe(230.0);
});

test('the customer is told when their order is rushed', function () {
    Notification::fake();
    $shop = queueShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = queueOrder($shop, ['user_id' => $customer->id, 'order_source' => 'online']);

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true]);

    Notification::assertSentTo($customer, OrderNotification::class, fn ($n) => $n->event === 'rush_marked');
});

test('a rush order cannot be rushed when the shop has no rush fee', function () {
    $shop = queueShop(rushFee: 0);
    $order = queueOrder($shop);

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true])->assertSessionHasErrors('rush');

    expect($order->fresh()->is_rush)->toBeFalse()
        ->and((float) $order->fresh()->total_amount)->toBe(180.0);
});

test('only an order that is still in the queue can be rushed', function () {
    $shop = queueShop();
    $order = queueOrder($shop, ['status' => 'completed']);

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true])->assertSessionHasErrors('rush');

    expect($order->fresh()->is_rush)->toBeFalse();
});

test('the rush choice is required', function () {
    $shop = queueShop();
    $order = queueOrder($shop);

    $this->actingAs($shop->owner)->patch(rushUrl($order), [])->assertSessionHasErrors('rush');
});

test('taking rush off restores the total and the place in line', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop);
    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true]);

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => false])->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->is_rush)->toBeFalse()
        ->and((float) $order->rush_fee)->toBe(0.0)
        ->and((float) $order->total_amount)->toBe(180.0)
        ->and($order->rush_marked_at)->toBeNull();
});

test('rush cannot be taken off once a payment is recorded', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop);
    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => true]);
    $this->actingAs($shop->owner)->post("/shop/operations/orders/{$order->payment_public_id}/payments", [
        'amount' => '100',
        'method' => 'cash',
    ])->assertSessionHasNoErrors();

    $this->actingAs($shop->owner)->patch(rushUrl($order), ['rush' => false])->assertSessionHasErrors('rush');

    expect($order->fresh()->is_rush)->toBeTrue()
        ->and((float) $order->fresh()->total_amount)->toBe(230.0);
});

test('weighing a rushed order keeps the rush fee in the total', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop, ['actual_weight_kg' => null, 'total_amount' => 0]);
    app(ShopOrderService::class)->setRush($order, true, $shop->owner);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), ['actual_weight_kg' => 3]);

    // 3 kg x 60 = 180, + 50 rush
    expect((float) $updated->total_amount)->toBe(230.0);
});

test('the rush fee appears on the receipt', function () {
    $shop = queueShop(rushFee: 50);
    $order = queueOrder($shop);
    app(ShopOrderService::class)->setRush($order, true, $shop->owner);

    $this->actingAs($shop->owner)->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shopOrder.is_rush', true)
            ->where('receipt.items', fn ($items) => collect($items)->contains(
                fn ($item) => $item['label'] === 'Rush fee' && (float) $item['amount'] === 50.0,
            )));
});

// ─── Who may rush ─────────────────────────────────────────────────────────────

test('another shop cannot rush my order', function () {
    $mine = queueShop('Mine');
    $theirs = queueShop('Theirs');
    $order = queueOrder($mine);

    $this->actingAs($theirs->owner)->patch(rushUrl($order), ['rush' => true])->assertNotFound();

    expect($order->fresh()->is_rush)->toBeFalse();
});

test('staff need the Operations manage permission to rush an order', function () {
    $shop = queueShop();
    $order = queueOrder($shop);

    $viewer = queueStaff($shop, ['view']);
    // The permission middleware turns the staff member away (a redirect or a 403).
    expect($this->actingAs($viewer)->patch(rushUrl($order, '/staff'), ['rush' => true])->status())->toBeIn([302, 403])
        ->and($order->fresh()->is_rush)->toBeFalse();

    $manager = queueStaff($shop, ['view', 'manage']);
    $this->actingAs($manager)->patch(rushUrl($order, '/staff'), ['rush' => true])->assertSessionHasNoErrors();
    expect($order->fresh()->is_rush)->toBeTrue();
});

test('staff of another shop cannot rush my order', function () {
    $mine = queueShop('Mine');
    $theirs = queueShop('Theirs');
    $order = queueOrder($mine);
    $outsider = queueStaff($theirs, ['view', 'manage']);

    $this->actingAs($outsider)->patch(rushUrl($order, '/staff'), ['rush' => true])->assertNotFound();

    expect($order->fresh()->is_rush)->toBeFalse();
});

test('a guest cannot rush an order', function () {
    $order = queueOrder(queueShop());

    $this->patch(rushUrl($order), ['rush' => true])->assertRedirect();

    expect($order->fresh()->is_rush)->toBeFalse();
});

// ─── Rush fee setting ─────────────────────────────────────────────────────────

test('the owner sets the shop rush fee', function () {
    $shop = queueShop(rushFee: 0);

    $this->actingAs($shop->owner)->patch('/shop/settings/rush-fee', ['rush_fee' => '75.5'])
        ->assertSessionHasNoErrors();

    expect((float) $shop->fresh()->rush_fee)->toBe(75.5);
});

test('an empty rush fee turns rush off', function () {
    $shop = queueShop(rushFee: 50);

    $this->actingAs($shop->owner)->patch('/shop/settings/rush-fee', ['rush_fee' => ''])
        ->assertSessionHasNoErrors();

    expect((float) $shop->fresh()->rush_fee)->toBe(0.0);
});

test('the rush fee must be a sensible amount', function (mixed $value) {
    $shop = queueShop(rushFee: 50);

    $this->actingAs($shop->owner)->patch('/shop/settings/rush-fee', ['rush_fee' => $value])
        ->assertSessionHasErrors('rush_fee');

    expect((float) $shop->fresh()->rush_fee)->toBe(50.0);
})->with(['negative' => [-5], 'too large' => [5001], 'not a number' => ['abc']]);

test('staff cannot change the rush fee', function () {
    $shop = queueShop(rushFee: 50);
    $staff = queueStaff($shop, ['view', 'manage']);

    expect($this->actingAs($staff)->patch('/shop/settings/rush-fee', ['rush_fee' => '1'])->status())->toBeIn([302, 403])
        ->and((float) $shop->fresh()->rush_fee)->toBe(50.0);
});

// ─── What the customer sees ───────────────────────────────────────────────────

test('the customer sees a rush badge but never a queue position', function () {
    $shop = queueShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = queueOrder($shop, ['user_id' => $customer->id, 'order_source' => 'online', 'is_rush' => true, 'rush_fee' => 50]);
    queueOrder($shop, ['created_at' => now()->subDay()]);

    $response = $this->actingAs($customer)->get('/user/orders')->assertOk();

    $row = collect($response->viewData('page')['props']['orders']['data'])->first();
    expect($row['is_rush'])->toBeTrue()
        ->and($row)->not->toHaveKey('queue_position');

    $detail = $this->actingAs($customer)->get("/user/orders/{$order->payment_public_id}")->assertOk();
    expect($detail->viewData('page')['props']['order'])->not->toHaveKey('queue_position');
});
