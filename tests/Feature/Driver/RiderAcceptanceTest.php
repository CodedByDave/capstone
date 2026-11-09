<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Notifications\DeliveryNotification;
use App\Notifications\DriverAssignmentNotification;
use App\Notifications\NoRiderAvailableNotification;
use App\Services\DeliveryTrackingService;
use App\Services\LogisticsService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('private');
});

/** A run handed to a rider who has not answered yet. */
function unanswered(Shop $shop, Rider $rider, array $overrides = []): Delivery
{
    return Delivery::create(array_merge([
        'shop_id' => $shop->id,
        'type' => 'delivery',
        'customer_name' => 'Andrea Santos',
        'customer_phone' => '09181234567',
        'delivery_address' => 'Unit 4B',
        'destination_lat' => 14.4,
        'destination_lng' => 120.9,
        'status' => 'assigned',
        'rider_id' => $rider->id,
        'assigned_at' => now(),
    ], $overrides));
}

function acceptanceOrder(Shop $shop, User $customer): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash '.uniqid(),
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create([
        'shop_id' => $shop->id,
        'user_id' => $customer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Andrea Santos',
        'pickup_type' => 'pickup',
        'deliver_to_customer' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => 5,
        'total_amount' => 350,
        'payment_status' => 'paid',
        'amount_paid' => 350,
        'status' => 'completed',
    ]);
}

function acceptanceCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

function waitingLeg(Shop $shop, ShopOrder $order, string $type): Delivery
{
    return Delivery::create([
        'shop_id' => $shop->id,
        'shop_order_id' => $order->id,
        'type' => $type,
        'customer_name' => 'Andrea Santos',
        'delivery_address' => 'Unit 4B',
        'status' => 'pending',
    ]);
}

// ── The server insists on an answer first ────────────────────────────────────

test('a rider cannot move a run they have not accepted', function (string $status) {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = unanswered($shop, $rider);

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => $status, 'delivery_photo' => riderPhoto(), 'notes' => 'x'])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh())
        ->status->toBe('assigned')
        ->accepted_at->toBeNull()
        ->picked_up_at->toBeNull();
})->with(['picked_up', 'delivered', 'failed']);

test('accepting lets the rider start, and a problem can then be reported', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $first = unanswered($shop, $rider);
    $driver = $rider->employee->user;

    $this->actingAs($driver)->post("/driver/jobs/{$first->public_id}/accept")->assertSessionHas('toast.type', 'success');
    $this->actingAs($driver)
        ->patch("/driver/jobs/{$first->public_id}/status", ['status' => 'picked_up'])
        ->assertSessionHas('toast.type', 'success');

    expect($first->fresh()->status)->toBe('picked_up');

    $second = unanswered($shop, $rider);
    $this->actingAs($driver)->post("/driver/jobs/{$second->public_id}/accept");
    $this->actingAs($driver)
        ->patch("/driver/jobs/{$second->public_id}/status", ['status' => 'failed', 'notes' => 'Flat tyre'])
        ->assertSessionHas('toast.type', 'success');

    expect($second->fresh()->status)->toBe('failed');
});

test('an accepted run still cannot skip the pickup', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = unanswered($shop, $rider, ['accepted_at' => now()]);

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered', 'delivery_photo' => riderPhoto()])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh()->status)->toBe('assigned')->and($run->fresh()->delivered_at)->toBeNull();
});

// ── Location waits for the answer ────────────────────────────────────────────

test('location is refused until the rider accepts, and accepted afterwards', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = unanswered($shop, $rider);
    $driver = $rider->employee->user;
    $url = "/driver/jobs/{$run->public_id}/location";

    $this->actingAs($driver)->postJson($url, ['lat' => 14.41, 'lng' => 120.91])->assertStatus(409);
    expect($run->fresh()->rider_lat)->toBeNull();

    $this->actingAs($driver)->post("/driver/jobs/{$run->public_id}/accept");

    $this->actingAs($driver)->postJson($url, ['lat' => 14.41, 'lng' => 120.91])->assertOk();
    expect((float) $run->fresh()->rider_lat)->toBe(14.41);
});

// ── What the customer sees and hears ─────────────────────────────────────────

test('before the rider accepts, the customer sees no rider at all', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $customer = acceptanceCustomer();
    $order = acceptanceOrder($shop, $customer);
    $run = unanswered($shop, $rider, ['shop_order_id' => $order->id, 'rider_lat' => 14.41, 'rider_lng' => 120.91, 'rider_located_at' => now()]);
    $tracking = app(DeliveryTrackingService::class);

    $before = $tracking->forCustomer($customer, $order->payment_public_id);

    expect($before['status'])->toBe('pending')
        ->and($before['is_active'])->toBeFalse()
        ->and($before['rider_name'])->toBeNull()
        ->and($before['rider_phone'])->toBeNull()
        ->and($before['vehicle'])->toBeNull()
        ->and($before['rider'])->toBeNull()
        ->and($before['timeline']['assigned_at'])->toBeNull();

    $this->actingAs($rider->employee->user)->post("/driver/jobs/{$run->public_id}/accept");

    $after = $tracking->forCustomer($customer, $order->payment_public_id);

    expect($after['status'])->toBe('assigned')
        ->and($after['is_active'])->toBeTrue()
        ->and($after['rider_name'])->toBe($rider->name)
        ->and($after['rider'])->not->toBeNull()
        ->and($after['timeline']['assigned_at'])->not->toBeNull();
});

test('the customer is told about the rider only when the rider accepts', function () {
    Notification::fake();
    $shop = driverShop();
    $rider = onlineRider($shop);
    $customer = acceptanceCustomer();
    $order = acceptanceOrder($shop, $customer);
    $run = waitingLeg($shop, $order, 'pickup');

    app(LogisticsService::class)->assignWaitingDeliveries($shop->id);

    expect($run->fresh()->rider_id)->toBe($rider->id);
    Notification::assertSentTo($rider->employee->user, DriverAssignmentNotification::class);
    Notification::assertNotSentTo($customer, DeliveryNotification::class);

    $this->actingAs($rider->employee->user)->post("/driver/jobs/{$run->public_id}/accept");

    Notification::assertSentTo($customer, DeliveryNotification::class, function (DeliveryNotification $notification) use ($customer) {
        return $notification->toDatabase($customer)['title'] === 'Rider on the way to collect';
    });
});

test('the shop giving a run to a rider does not tell the customer yet', function () {
    Notification::fake();
    $shop = driverShop();
    $rider = onlineRider($shop);
    $customer = acceptanceCustomer();
    $run = waitingLeg($shop, acceptanceOrder($shop, $customer), 'delivery');

    app(LogisticsService::class)->updateStatus($shop->owner, $run, ['status' => 'assigned', 'rider_id' => $rider->id]);

    Notification::assertNotSentTo($customer, DeliveryNotification::class);
});

// ── When no rider can take the run ───────────────────────────────────────────

test('with no rider, the customer is told to drop off or collect at the shop', function (string $type, string $expected) {
    $shop = driverShop();
    $customer = acceptanceCustomer();
    $order = acceptanceOrder($shop, $customer);
    waitingLeg($shop, $order, $type);
    $notice = "No rider is available right now. Please {$expected} at Driver Laundry.";

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('order.no_rider_notice', $notice));

    $this->actingAs($customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertJsonPath("{$type}.no_rider_notice", $notice);
})->with([
    'pickup' => ['pickup', 'drop off your laundry'],
    'delivery' => ['delivery', 'collect your laundry'],
]);

test('the no rider notification tells the customer what to do', function () {
    $shop = driverShop();
    $customer = acceptanceCustomer();
    $run = waitingLeg($shop, acceptanceOrder($shop, $customer), 'pickup');
    $run->load('order.shop');

    $body = (new NoRiderAvailableNotification($run, NoRiderAvailableNotification::FOR_CUSTOMER))->toDatabase($customer)['body'];

    expect($body)->toContain('Please drop off your laundry at Driver Laundry.');
});

test('no notice while a rider has the run, even before they answer', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $customer = acceptanceCustomer();
    $order = acceptanceOrder($shop, $customer);
    $run = unanswered($shop, $rider, ['shop_order_id' => $order->id, 'type' => 'pickup']);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('order.no_rider_notice', null));

    $run->update(['accepted_at' => now()]);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('order.no_rider_notice', null));
});

test('no notice on a run a courier handles', function () {
    $shop = driverShop();
    $customer = acceptanceCustomer();
    $order = acceptanceOrder($shop, $customer);
    waitingLeg($shop, $order, 'delivery')->update(['courier' => 'lalamove']);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('order.no_rider_notice', null));
});
