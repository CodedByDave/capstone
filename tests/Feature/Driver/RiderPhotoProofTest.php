<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\DeliveryPhoto;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\ShopService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('private');
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function proofRun(Shop $shop, Rider $rider, string $type, string $status, array $overrides = []): Delivery
{
    return Delivery::create(array_merge([
        'shop_id' => $shop->id,
        'type' => $type,
        'customer_name' => 'Andrea Santos',
        'customer_phone' => '09181234567',
        'delivery_address' => 'Unit 4B, Palm Residences',
        'destination_lat' => 14.4,
        'destination_lng' => 120.9,
        'status' => $status,
        'rider_id' => $rider->id,
        'assigned_at' => now(),
        'accepted_at' => now(),
    ], $overrides));
}

function proofOrder(Shop $shop, array $overrides = []): ShopOrder
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
        'customer_name' => 'Andrea Santos',
        'pickup_type' => 'pickup',
        'deliver_to_customer' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => 5,
        'total_amount' => 350,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'completed',
    ], $overrides));
}

function kindsOn(Delivery $run): array
{
    return DeliveryPhoto::where('delivery_id', $run->id)->orderBy('kind')->pluck('kind')->all();
}

// ── Picking up from the customer ─────────────────────────────────────────────

test('a pickup from the customer is refused without a photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = proofRun($shop, $rider, 'pickup', 'assigned');

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up'])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh()->status)->toBe('assigned')
        ->and($run->fresh()->picked_up_at)->toBeNull()
        ->and(kindsOn($run))->toBe([]);
});

test('a pickup photo is kept privately on the run and the shop can open it', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = proofRun($shop, $rider, 'pickup', 'assigned');
    $driver = $rider->employee->user;

    $this->actingAs($driver)
        ->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('job.requires_pickup_photo', true)
            ->where('job.requires_delivery_photo', false));

    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up', 'proof_photo' => riderPhoto()])
        ->assertSessionHas('toast.type', 'success');

    $photo = DeliveryPhoto::where('delivery_id', $run->id)->sole();

    expect($run->fresh()->status)->toBe('picked_up')
        ->and($photo->kind)->toBe('pickup')
        ->and($photo->uploaded_by)->toBe($driver->id);
    Storage::disk('private')->assertExists($photo->path);

    $this->actingAs($shop->owner)->get("/shop/logistics/photos/{$photo->public_id}")->assertOk();
});

test('collecting the bags from the shop for a delivery needs no photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = proofRun($shop, $rider, 'delivery', 'assigned');

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up'])
        ->assertSessionHas('toast.type', 'success');

    expect($run->fresh()->status)->toBe('picked_up')->and(kindsOn($run))->toBe([]);
});

test('a pickup run reaching the shop needs no second photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = proofRun($shop, $rider, 'pickup', 'picked_up');

    $this->actingAs($rider->employee->user)
        ->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('job.requires_delivery_photo', false));

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered'])
        ->assertSessionHas('toast.type', 'success');

    expect($run->fresh()->status)->toBe('delivered');
});

// ── Handing over to the customer ─────────────────────────────────────────────

test('a delivery with nothing to collect is refused without a photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $order = proofOrder($shop, ['payment_status' => 'paid', 'amount_paid' => 350]);
    $run = proofRun($shop, $rider, 'delivery', 'picked_up', ['shop_order_id' => $order->id]);
    $driver = $rider->employee->user;

    $this->actingAs($driver)
        ->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('job.requires_delivery_photo', true));

    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered'])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh()->status)->toBe('picked_up')->and($run->fresh()->delivered_at)->toBeNull();

    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered', 'delivery_photo' => riderPhoto()])
        ->assertSessionHas('toast.type', 'success');

    $photo = DeliveryPhoto::where('delivery_id', $run->id)->sole();

    expect($run->fresh()->status)->toBe('delivered')->and($photo->kind)->toBe('delivery');
    Storage::disk('private')->assertExists($photo->path);
});

test('a rider who collects nothing on an unpaid order still needs the handover photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $order = proofOrder($shop);
    $run = proofRun($shop, $rider, 'delivery', 'picked_up', ['shop_order_id' => $order->id]);

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered', 'collected_amount' => '0'])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh()->status)->toBe('picked_up');
});

test('collecting cash keeps two photos, one of the cash and one of the handover', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $order = proofOrder($shop);
    $run = proofRun($shop, $rider, 'delivery', 'picked_up', ['shop_order_id' => $order->id]);
    $driver = $rider->employee->user;

    // The cash photo alone is not enough, and nothing is recorded.
    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", [
            'status' => 'delivered', 'collected_amount' => '350', 'proof_photo' => riderPhoto('cash.png'),
        ])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh()->status)->toBe('picked_up')
        ->and(ShopOrderPayment::where('shop_order_id', $order->id)->count())->toBe(0)
        ->and(kindsOn($run))->toBe([]);

    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", [
            'status' => 'delivered',
            'collected_amount' => '350',
            'proof_photo' => riderPhoto('cash.png'),
            'delivery_photo' => riderPhoto('handover.png'),
        ])
        ->assertSessionHas('toast.type', 'success');

    expect($run->fresh()->status)->toBe('delivered')
        ->and(kindsOn($run))->toBe(['cash', 'delivery'])
        ->and($order->fresh()->payment_status)->toBe('paid');
});

test('reporting a failed run needs no photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = proofRun($shop, $rider, 'delivery', 'picked_up');

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'failed', 'notes' => 'Nobody home'])
        ->assertSessionHas('toast.type', 'success');

    expect($run->fresh()->status)->toBe('failed');
});

// ── What a refused photo tells the rider ─────────────────────────────────────

test('a photo that is too big or not an image comes back as an error the rider can read', function (string $field) {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $status = $field === 'delivery_photo' ? 'picked_up' : 'assigned';
    $run = proofRun($shop, $rider, $field === 'delivery_photo' ? 'delivery' : 'pickup', $status);
    $next = $field === 'delivery_photo' ? 'delivered' : 'picked_up';
    $driver = $rider->employee->user;

    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", [
            'status' => $next,
            $field => UploadedFile::fake()->create('big.jpg', 6000, 'image/jpeg'),
        ])
        ->assertSessionHasErrors($field);

    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", [
            'status' => $next,
            $field => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])
        ->assertSessionHasErrors($field);

    expect($run->fresh()->status)->toBe($status)->and(kindsOn($run))->toBe([]);
})->with(['proof_photo', 'delivery_photo']);

test('a required rider photo is not held up by the shop reaching its photo limit', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = proofRun($shop, $rider, 'delivery', 'picked_up');

    foreach (range(1, 8) as $number) {
        DeliveryPhoto::create(['delivery_id' => $run->id, 'uploaded_by' => $shop->owner_id, 'path' => "proofs/{$number}.png", 'kind' => 'proof']);
    }

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered', 'delivery_photo' => riderPhoto()])
        ->assertSessionHas('toast.type', 'success');

    expect($run->fresh()->status)->toBe('delivered');
});

// ── What the customer sees ───────────────────────────────────────────────────

test('the customer sees the pickup photos on their order page and can open them', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = proofOrder($shop, ['user_id' => $customer->id, 'status' => 'in_progress']);
    $run = proofRun($shop, $rider, 'pickup', 'assigned', ['shop_order_id' => $order->id]);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page->component('user/OrderDetail')->has('order.pickup_photos', 0));

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up', 'proof_photo' => riderPhoto()])
        ->assertSessionHas('toast.type', 'success');

    $photo = DeliveryPhoto::where('delivery_id', $run->id)->sole();

    // A delivery back to the customer must not hide the proof of the pickup.
    proofRun($shop, $rider, 'delivery', 'assigned', ['shop_order_id' => $order->id]);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->has('order.pickup_photos', 1)
            ->where('order.pickup_photos.0.public_id', $photo->public_id)
            ->missing('order.pickup_photos.0.path'));

    $this->actingAs($customer)->get("/user/orders/delivery-photos/{$photo->public_id}")->assertOk();
});

test('another customer cannot open a pickup photo or see it through someone else\'s order', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $other = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = proofOrder($shop, ['user_id' => $customer->id]);
    $run = proofRun($shop, $rider, 'pickup', 'assigned', ['shop_order_id' => $order->id]);

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up', 'proof_photo' => riderPhoto()]);

    $photo = DeliveryPhoto::where('delivery_id', $run->id)->sole();

    $this->actingAs($other)->get("/user/orders/delivery-photos/{$photo->public_id}")->assertNotFound();
    $this->actingAs($other)->get("/user/orders/{$order->payment_public_id}")->assertNotFound();
});

// ── The shop's override ──────────────────────────────────────────────────────

test('the shop can still move a run on the board without a photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $pickup = proofRun($shop, $rider, 'pickup', 'assigned');
    $delivery = proofRun($shop, $rider, 'delivery', 'picked_up');

    $this->actingAs($shop->owner)
        ->patch("/shop/logistics/{$pickup->public_id}/status", ['status' => 'picked_up', 'rider_id' => $rider->id, 'courier' => 'own_rider'])
        ->assertSessionHasNoErrors();
    $this->actingAs($shop->owner)
        ->patch("/shop/logistics/{$delivery->public_id}/status", ['status' => 'delivered', 'rider_id' => $rider->id, 'courier' => 'own_rider'])
        ->assertSessionHasNoErrors();

    expect($pickup->fresh()->status)->toBe('picked_up')
        ->and($delivery->fresh()->status)->toBe('delivered')
        ->and(kindsOn($pickup))->toBe([])
        ->and(kindsOn($delivery))->toBe([]);
});
