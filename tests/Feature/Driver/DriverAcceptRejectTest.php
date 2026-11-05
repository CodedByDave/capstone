<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\DeliveryRejection;
use App\Models\Rider;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\DeliveryRejectedNotification;
use App\Notifications\DriverAssignmentNotification;
use App\Notifications\NoRiderAvailableNotification;
use App\Services\LogisticsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

function proofImage(): UploadedFile
{
    return UploadedFile::fake()->createWithContent(
        'proof.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
    );
}

function rejectOrder(App\Models\Shop $shop, array $overrides = []): ShopOrder
{
    $service = App\Models\ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash & Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Andrea Santos',
        'pickup_type' => 'walk_in',
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

function waitingRun(App\Models\Shop $shop, array $overrides = []): Delivery
{
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = rejectOrder($shop, ['user_id' => $customer->id]);

    return Delivery::create(array_merge([
        'shop_id' => $shop->id,
        'shop_order_id' => $order->id,
        'type' => 'delivery',
        'customer_name' => 'Andrea Santos',
        'customer_phone' => '09181234567',
        'delivery_address' => 'Unit 4B',
        'status' => 'pending',
    ], $overrides));
}

test('a driver is notified when a waiting order is given to them', function () {
    Notification::fake();
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = waitingRun($shop);

    app(LogisticsService::class)->assignWaitingDeliveries($shop->id);

    expect($run->fresh()->rider_id)->toBe($rider->id)
        ->and($run->fresh()->accepted_at)->toBeNull();
    Notification::assertSentTo($rider->employee->user, DriverAssignmentNotification::class);
});

test('a driver accepts an assigned order', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = waitingRun($shop, ['status' => 'assigned', 'rider_id' => $rider->id, 'assigned_at' => now()]);

    $this->actingAs($rider->employee->user)
        ->post("/driver/jobs/{$run->public_id}/accept")
        ->assertSessionHasNoErrors();

    expect($run->fresh()->accepted_at)->not->toBeNull();

    // Answering twice is refused.
    $this->actingAs($rider->employee->user)
        ->post("/driver/jobs/{$run->public_id}/accept")
        ->assertSessionHas('toast.type', 'error');
});

test('a driver cannot accept another drivers order', function () {
    $shop = driverShop();
    $rider = onlineRider($shop, 'One');
    $other = onlineRider($shop, 'Two');
    $run = waitingRun($shop, ['status' => 'assigned', 'rider_id' => $rider->id, 'assigned_at' => now()]);

    $this->actingAs($other->employee->user)
        ->post("/driver/jobs/{$run->public_id}/accept")
        ->assertNotFound();
});

test('rejecting needs a reason and a proof photo', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = waitingRun($shop, ['status' => 'assigned', 'rider_id' => $rider->id, 'assigned_at' => now()]);
    $url = "/driver/jobs/{$run->public_id}/reject";

    $this->actingAs($rider->employee->user)->post($url, ['reason' => 'emergency'])
        ->assertSessionHasErrors('proof_photo');
    $this->actingAs($rider->employee->user)->post($url, ['reason' => 'other', 'proof_photo' => proofImage()])
        ->assertSessionHasErrors('note');
    $this->actingAs($rider->employee->user)->post($url, ['proof_photo' => proofImage()])
        ->assertSessionHasErrors('reason');

    expect($run->fresh()->rider_id)->toBe($rider->id);
});

test('a rejected order goes to the next driver and the shop is told why', function () {
    Notification::fake();
    Storage::fake('private');
    $shop = driverShop();
    $first = onlineRider($shop, 'First', true, now()->subHour());
    $second = onlineRider($shop, 'Second', true, now()->subMinutes(10));
    $run = waitingRun($shop, ['status' => 'assigned', 'rider_id' => $first->id, 'assigned_at' => now()]);

    $this->actingAs($first->employee->user)
        ->post("/driver/jobs/{$run->public_id}/reject", [
            'reason' => 'emergency',
            'note' => 'Family emergency',
            'proof_photo' => proofImage(),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/driver/jobs');

    $rejection = DeliveryRejection::firstOrFail();
    Storage::disk('private')->assertExists($rejection->photo_path);

    expect($rejection->rider_id)->toBe($first->id)
        ->and($rejection->reason)->toBe('emergency')
        ->and($run->fresh()->rider_id)->toBe($second->id)
        ->and($run->fresh()->status)->toBe('assigned')
        ->and($run->fresh()->accepted_at)->toBeNull()
        ->and($first->fresh()->is_online)->toBeFalse();

    Notification::assertSentTo($shop->owner, DeliveryRejectedNotification::class);
    Notification::assertSentTo($second->employee->user, DriverAssignmentNotification::class);
});

test('a driver who rejected an order is not offered it again', function () {
    Notification::fake();
    Storage::fake('private');
    $shop = driverShop();
    $only = onlineRider($shop, 'Only');
    $run = waitingRun($shop, ['status' => 'assigned', 'rider_id' => $only->id, 'assigned_at' => now()]);

    $this->actingAs($only->employee->user)
        ->post("/driver/jobs/{$run->public_id}/reject", ['reason' => 'vehicle_problem', 'proof_photo' => proofImage()]);

    // The driver is back online (the problem is fixed) but still does not get the run they refused.
    $only->fresh()->update(['is_online' => true]);
    app(LogisticsService::class)->assignWaitingDeliveries($shop->id);

    expect($run->fresh()->status)->toBe('pending')
        ->and($run->fresh()->rider_id)->toBeNull();
    // With nobody else free, the customer and the shop are told.
    Notification::assertSentTo($run->order->customer, NoRiderAvailableNotification::class);
    Notification::assertSentTo($shop->owner, NoRiderAvailableNotification::class);
});

test('an order already picked up cannot be rejected', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = waitingRun($shop, ['status' => 'picked_up', 'rider_id' => $rider->id, 'assigned_at' => now()]);

    $this->actingAs($rider->employee->user)
        ->post("/driver/jobs/{$run->public_id}/reject", ['reason' => 'emergency', 'proof_photo' => proofImage()])
        ->assertSessionHas('toast.type', 'error');

    expect($run->fresh()->rider_id)->toBe($rider->id);
});

test('the customer is told when no rider is available for their order', function () {
    Notification::fake();
    $shop = driverShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = rejectOrder($shop, ['user_id' => $customer->id, 'pickup_type' => 'pickup', 'status' => 'pending']);

    // No rider is online.
    $delivery = app(LogisticsService::class)->dispatchPickupRun($order);

    expect($delivery?->status)->toBe('pending');
    Notification::assertSentTo($customer, NoRiderAvailableNotification::class);
    Notification::assertSentTo($shop->owner, NoRiderAvailableNotification::class);
    expect(app(LogisticsService::class)->isWaitingForRider($order))->toBeTrue();
});

test('only the shop can open a rejection proof photo', function () {
    Storage::fake('private');
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = waitingRun($shop, ['status' => 'assigned', 'rider_id' => $rider->id, 'assigned_at' => now()]);
    $this->actingAs($rider->employee->user)
        ->post("/driver/jobs/{$run->public_id}/reject", ['reason' => 'emergency', 'proof_photo' => proofImage()]);
    $rejection = DeliveryRejection::firstOrFail();

    $gates = [App\Http\Middleware\EnforceOwnerPlatformPermissions::class, App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted::class];
    $this->withoutMiddleware($gates)->actingAs($shop->owner)
        ->get("/shop/logistics/rejections/{$rejection->public_id}/photo")
        ->assertOk();

    $other = driverShop();
    $this->withoutMiddleware($gates)->actingAs($other->owner)
        ->get("/shop/logistics/rejections/{$rejection->public_id}/photo")
        ->assertNotFound();
});
