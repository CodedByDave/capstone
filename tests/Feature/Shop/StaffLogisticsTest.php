<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Rider;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\LogisticsService;
use App\Services\ShopOrderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function dispatchShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Dispatch Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'setup_completed_at' => now(),
    ], $overrides));
}

/** A staff member of the shop holding the given Logistics actions. */
function dispatcher(Shop $shop, array $actions, string $role = 'dispatcher'): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'DSP-'.$user->id,
        'first_name' => 'Dis',
        'last_name' => 'Patcher',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => $role]);

    foreach ($actions as $action) {
        RolePermission::create([
            'shop_id' => $shop->id,
            'role' => $role,
            'module' => 'Logistics',
            'action' => $action,
        ]);
    }

    return $user;
}

test('permitted staff can assign a rider to a delivery', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $rider = onlineRider($shop, 'Rico');
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", ['status' => 'assigned', 'rider_id' => $rider->id])
        ->assertRedirect();

    expect($delivery->fresh())
        ->status->toBe('assigned')
        ->rider_id->toBe($rider->id)
        ->assigned_at->not->toBeNull();
});

test('staff without the Logistics permission are turned away', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, []);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)->get('/staff/logistics')->assertRedirect(route('staff.dashboard'));
    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", ['status' => 'delivered'])
        ->assertRedirect(route('staff.dashboard'));

    expect($delivery->fresh()->status)->toBe('pending');
});

test('view-only staff cannot assign riders or remove deliveries', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view']);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)->get('/staff/logistics')->assertOk();
    $this->actingAs($staff)->patch("/staff/logistics/{$delivery->public_id}/status", ['status' => 'assigned'])
        ->assertRedirect(route('staff.dashboard'));
    $this->actingAs($staff)->delete("/staff/logistics/{$delivery->public_id}")
        ->assertRedirect(route('staff.dashboard'));

    expect(Delivery::find($delivery->id))->not->toBeNull();
});

test('staff cannot reach another shop\'s deliveries or assign its riders', function () {
    $shop = dispatchShop();
    $other = dispatchShop(['shop_name' => 'Other Laundry']);
    $staff = dispatcher($shop, ['view', 'update']);
    $foreignDelivery = Delivery::create(['shop_id' => $other->id, 'customer_name' => 'Zed', 'status' => 'pending']);
    $foreignRider = onlineRider($other, 'Rex');
    $ownDelivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$foreignDelivery->public_id}/status", ['status' => 'assigned'])
        ->assertForbidden();

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$ownDelivery->public_id}/status", ['status' => 'assigned', 'rider_id' => $foreignRider->id])
        ->assertSessionHasErrors('rider_id');

    expect($foreignDelivery->fresh()->status)->toBe('pending')
        ->and($ownDelivery->fresh()->rider_id)->toBeNull();
});

test('staff cannot dispatch when the shop does not offer delivery', function () {
    $shop = dispatchShop(['offers_delivery' => false]);
    $staff = dispatcher($shop, ['view']);

    $this->actingAs($staff)->get('/staff/logistics')->assertForbidden();
});

test('Logistics appears in Roles & Permissions only for shops that offer delivery', function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);

    $withDelivery = dispatchShop();
    $withoutDelivery = dispatchShop(['offers_delivery' => false]);

    foreach ([[$withDelivery, true], [$withoutDelivery, false]] as [$shop, $expected]) {
        $this->actingAs($shop->owner)
            ->get('/shop/permission')
            ->assertInertia(fn ($page) => $page->where(
                'purchasedModules',
                fn ($modules) => collect($modules)->contains('name', 'Logistics') === $expected,
            ));
    }
});

// ── Completed pickup orders reach the board ──────────────────────────────────

function autoOrder(Shop $shop, array $overrides = []): ShopOrder
{
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'user_id' => $customer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ONL-'.strtoupper(uniqid()),
        'customer_name' => 'Maria Cruz',
        'customer_phone' => '09181234567',
        'customer_address' => '12 Rizal St, Imus',
        'pickup_type' => 'pickup',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'unpaid',
        'status' => 'in_progress',
    ], $overrides));
}

test('completing a pickup order that asked for delivery puts it on the board with the customer details and a free rider', function () {
    $shop = dispatchShop();
    $busy = onlineRider($shop, 'Busy');
    Delivery::create(['shop_id' => $shop->id, 'rider_id' => $busy->id, 'customer_name' => 'X', 'status' => 'picked_up']);
    $free = onlineRider($shop, 'Free');
    $order = autoOrder($shop, ['deliver_to_customer' => true]);

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    $delivery = Delivery::where('shop_order_id', $order->id)->first();
    expect($delivery)
        ->customer_name->toBe('Maria Cruz')
        ->delivery_address->toBe('12 Rizal St, Imus')
        ->customer_email->toBe($order->customer->email)
        ->status->toBe('assigned')
        ->rider_id->toBe($free->id);
});

test('a pickup order waits as pending when every rider is on the road', function () {
    $shop = dispatchShop();
    $busy = onlineRider($shop, 'Busy');
    Delivery::create(['shop_id' => $shop->id, 'rider_id' => $busy->id, 'customer_name' => 'X', 'status' => 'assigned']);
    $order = autoOrder($shop, ['deliver_to_customer' => true]);

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->first())
        ->status->toBe('pending')
        ->rider_id->toBeNull();
});

test('inactive riders are never auto assigned', function () {
    $shop = dispatchShop();
    Rider::create(['shop_id' => $shop->id, 'name' => 'Off', 'status' => 'inactive']);
    $order = autoOrder($shop, ['deliver_to_customer' => true]);

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->first()->rider_id)->toBeNull();
});

test('walk-in orders and shops without delivery create no delivery', function () {
    $shop = dispatchShop();
    $walkIn = autoOrder($shop, ['pickup_type' => 'walk_in']);
    $noDelivery = autoOrder(dispatchShop(['offers_delivery' => false]));

    app(ShopOrderService::class)->updateStatus($walkIn, 'completed');
    app(ShopOrderService::class)->updateStatus($noDelivery, 'completed');

    expect(Delivery::count())->toBe(0);
});

test('completing an order twice creates one delivery', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['deliver_to_customer' => true]);

    app(ShopOrderService::class)->updateStatus($order, 'completed');
    app(ShopOrderService::class)->updateStatus($order->fresh(), 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->count())->toBe(1);
});

// ── Lalamove bookings ────────────────────────────────────────────────────────

test('a delivery can be handed to Lalamove with the booking details and no shop rider', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $rider = onlineRider($shop, 'Rico');
    $delivery = Delivery::create(['shop_id' => $shop->id, 'rider_id' => $rider->id, 'customer_name' => 'Ana', 'status' => 'assigned']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", [
            'status' => 'assigned',
            'rider_id' => $rider->id,
            'courier' => 'lalamove',
            'courier_reference' => 'LLM-12345',
            'courier_tracking_url' => 'https://share.lalamove.com/track/abc',
            'courier_driver_name' => 'Juan Driver',
            'courier_driver_phone' => '09171234567',
            'courier_vehicle_plate' => 'ABC 123',
            'courier_cost' => 185.5,
        ])
        ->assertSessionHasNoErrors();

    expect($delivery->fresh())
        ->courier->toBe('lalamove')
        ->rider_id->toBeNull()
        ->courier_reference->toBe('LLM-12345')
        ->courier_driver_name->toBe('Juan Driver')
        ->courier_vehicle_plate->toBe('ABC 123')
        ->courier_cost->toBe('185.50');
});

test('a Lalamove booking needs its reference once it is booked, and only a secure tracking link', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", ['status' => 'assigned', 'courier' => 'lalamove'])
        ->assertSessionHasErrors('courier_reference');

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", [
            'status' => 'pending',
            'courier' => 'lalamove',
            'courier_tracking_url' => 'http://insecure.example/track',
        ])
        ->assertSessionHasErrors('courier_tracking_url');

    expect($delivery->fresh()->courier)->toBe('own_rider');
});

test('a waiting run with no rider is booked with Lalamove from the Book a Lalamove dialog', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    // What the dialog sends: no rider, the run moves from pending to assigned.
    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", [
            'status' => 'assigned',
            'courier' => 'lalamove',
            'courier_reference' => 'LLM-777',
            'courier_tracking_url' => 'https://share.lalamove.com/track/xyz',
            'courier_driver_name' => null,
            'courier_driver_phone' => null,
            'courier_vehicle_plate' => null,
            'courier_cost' => null,
            'notes' => null,
        ])
        ->assertSessionHasNoErrors();

    expect($delivery->fresh())
        ->status->toBe('assigned')
        ->courier->toBe('lalamove')
        ->rider_id->toBeNull()
        ->courier_reference->toBe('LLM-777')
        ->assigned_at->not->toBeNull();
});

test('moving a Lalamove run forward in one click keeps its booking details', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $delivery = Delivery::create([
        'shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'assigned',
        'courier' => 'lalamove', 'courier_reference' => 'LLM-9', 'courier_driver_name' => 'Juan', 'courier_cost' => 120,
    ]);

    // The row's next-step button sends only the new status.
    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", ['status' => 'picked_up'])
        ->assertSessionHasNoErrors();

    expect($delivery->fresh())
        ->status->toBe('picked_up')
        ->courier->toBe('lalamove')
        ->courier_reference->toBe('LLM-9')
        ->courier_driver_name->toBe('Juan')
        ->courier_cost->toBe('120.00');
});

test('going back to an own rider clears the courier details', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $rider = onlineRider($shop, 'Rico');
    $delivery = Delivery::create([
        'shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'assigned',
        'courier' => 'lalamove', 'courier_reference' => 'LLM-1', 'courier_cost' => 90,
    ]);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->public_id}/status", [
            'status' => 'assigned', 'courier' => 'own_rider', 'rider_id' => $rider->id,
        ])
        ->assertSessionHasNoErrors();

    expect($delivery->fresh())
        ->courier->toBe('own_rider')
        ->rider_id->toBe($rider->id)
        ->courier_reference->toBeNull()
        ->courier_cost->toBeNull();
});

// ── Proof photos ─────────────────────────────────────────────────────────────

/** A real 1x1 PNG, because the test PHP has no GD extension to draw one. */
function proofImage(string $name = 'proof.png', ?int $reportedKb = null): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    $file = UploadedFile::fake()->createWithContent($name, $png);

    return $reportedKb ? $file->size($reportedKb) : $file;
}

function proofSetup(): array
{
    Storage::fake('private');
    $shop = dispatchShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = autoOrder($shop, ['user_id' => $customer->id, 'status' => 'completed']);
    $delivery = Delivery::create([
        'shop_id' => $shop->id, 'shop_order_id' => $order->id, 'customer_name' => 'Maria', 'status' => 'picked_up',
    ]);

    return [$shop, $customer, $delivery];
}

test('permitted staff upload proof photos at any delivery status and the customer can open them', function () {
    [$shop, $customer, $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update']);

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->public_id}/photos", [
            'photos' => [proofImage('door.png'), proofImage('bag.png')],
        ])
        ->assertSessionHasNoErrors();

    $photos = $delivery->photos()->get();
    expect($photos)->toHaveCount(2);
    Storage::disk('private')->assertExists($photos[0]->path);

    $this->actingAs($staff)->get("/staff/logistics/photos/{$photos[0]->public_id}")->assertOk();
    $this->actingAs($customer)->get("/user/orders/delivery-photos/{$photos[0]->public_id}")->assertOk();
});

test('proof photos stay private to the shop and the orders customer', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update']);
    $this->actingAs($staff)->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('a.png')]]);
    $photo = $delivery->photos()->first();

    $otherCustomer = User::factory()->create(['role' => AccountType::Customer->value]);
    $otherStaff = dispatcher(dispatchShop(['shop_name' => 'Other Laundry']), ['view', 'update']);

    $this->actingAs($otherCustomer)->get("/user/orders/delivery-photos/{$photo->public_id}")->assertNotFound();
    $this->actingAs($otherStaff)->get("/staff/logistics/photos/{$photo->public_id}")->assertNotFound();
    $this->actingAs($otherStaff)
        ->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('b.png')]])
        ->assertForbidden();
});

test('proof uploads accept only small images, a few at a time', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update']);

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')]])
        ->assertSessionHasErrors('photos.0');

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('big.png', 6000)]])
        ->assertSessionHasErrors('photos.0');

    foreach (range(1, 2) as $i) {
        $this->actingAs($staff)->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => array_map(
            fn ($n) => proofImage("p{$i}{$n}.png"), range(1, 4),
        )])->assertSessionHasNoErrors();
    }

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('extra.png')]])
        ->assertSessionHasErrors('photos');

    expect($delivery->photos()->count())->toBe(8);
});

test('only staff with the update permission upload and only with delete permission remove proof', function () {
    [$shop, , $delivery] = proofSetup();
    $viewer = dispatcher($shop, ['view']);

    $this->actingAs($viewer)
        ->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('a.png')]])
        ->assertRedirect(route('staff.dashboard'));
    expect($delivery->photos()->count())->toBe(0);

    $uploader = dispatcher($shop, ['view', 'update'], 'uploader');
    $this->actingAs($uploader)->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('a.png')]]);
    $photo = $delivery->photos()->first();

    $this->actingAs($uploader)->delete("/staff/logistics/photos/{$photo->public_id}")->assertRedirect(route('staff.dashboard'));
    expect($delivery->photos()->count())->toBe(1);

    $remover = dispatcher($shop, ['view', 'archive'], 'remover');
    $this->actingAs($remover)->delete("/staff/logistics/photos/{$photo->public_id}")->assertSessionHasNoErrors();

    expect($delivery->photos()->count())->toBe(0);
    Storage::disk('private')->assertMissing($photo->path);
});

test('archiving a delivery keeps the record and its proof, and the shop can restore it', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update', 'archive']);
    $this->actingAs($staff)->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('a.png')]]);
    $photo = $delivery->photos()->first();

    $this->actingAs($staff)->delete("/staff/logistics/{$delivery->public_id}")->assertSessionHasNoErrors();

    expect(Delivery::find($delivery->id))->toBeNull()
        ->and(Delivery::onlyTrashed()->find($delivery->id))->not->toBeNull();
    Storage::disk('private')->assertExists($photo->path);

    // The archive lists it, and its proof photo still opens for the shop.
    $this->actingAs($staff)->get('/staff/logistics?archived=1')
        ->assertInertia(fn ($page) => $page
            ->where('showArchived', true)
            ->where('deliveries.data.0.public_id', $delivery->public_id));
    $this->actingAs($staff)->get("/staff/logistics/photos/{$photo->public_id}")->assertOk();
    // An archived run is read-only.
    $this->actingAs($staff)->delete("/staff/logistics/photos/{$photo->public_id}")->assertNotFound();

    $this->actingAs($staff)->post("/staff/logistics/{$delivery->public_id}/restore")->assertSessionHasNoErrors();

    expect(Delivery::find($delivery->id))->not->toBeNull();
});

test('a delivery is not restored while its order already has another active run of that kind', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'archive']);
    $delivery->delete();
    Delivery::create([
        'shop_id' => $shop->id, 'shop_order_id' => $delivery->shop_order_id, 'customer_name' => 'Maria', 'status' => 'pending',
    ]);

    $this->actingAs($staff)->post("/staff/logistics/{$delivery->public_id}/restore")->assertSessionHasErrors('delivery');

    expect(Delivery::find($delivery->id))->toBeNull();
});

test('staff cannot restore another shop\'s archived delivery', function () {
    $shop = dispatchShop();
    $other = dispatchShop(['shop_name' => 'Other Laundry']);
    $staff = dispatcher($shop, ['view', 'archive']);
    $foreign = Delivery::create(['shop_id' => $other->id, 'customer_name' => 'Zed', 'status' => 'pending']);
    $foreign->delete();

    $this->actingAs($staff)->post("/staff/logistics/{$foreign->public_id}/restore")->assertNotFound();

    expect(Delivery::find($foreign->id))->toBeNull();
});

test('only a permanent delete removes the proof files', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update']);
    $this->actingAs($staff)->post("/staff/logistics/{$delivery->public_id}/photos", ['photos' => [proofImage('a.png')]]);
    $path = $delivery->photos()->first()->path;

    $delivery->forceDelete();

    Storage::disk('private')->assertMissing($path);
});

// ── Pickup runs (collecting the laundry from the customer) ───────────────────

test('a new pickup order gets a pickup run with the customer details and a free rider', function () {
    $shop = dispatchShop();
    $busy = onlineRider($shop, 'Busy');
    Delivery::create(['shop_id' => $shop->id, 'rider_id' => $busy->id, 'customer_name' => 'X', 'status' => 'assigned']);
    $free = onlineRider($shop, 'Free');
    $order = autoOrder($shop, ['status' => 'pending']);

    app(LogisticsService::class)->dispatchPickupRun($order);

    $run = Delivery::where('shop_order_id', $order->id)->first();
    expect($run)
        ->type->toBe('pickup')
        ->customer_name->toBe('Maria Cruz')
        ->customer_phone->not->toBeNull()
        ->delivery_address->toBe('12 Rizal St, Imus')
        ->status->toBe('assigned')
        ->rider_id->toBe($free->id);
});

test('a pickup run waits as pending when no rider is free', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['status' => 'pending']);

    app(LogisticsService::class)->dispatchPickupRun($order);

    expect(Delivery::where('shop_order_id', $order->id)->first())
        ->type->toBe('pickup')
        ->status->toBe('pending')
        ->rider_id->toBeNull();
});

test('walk-in orders, finished orders and non-delivery shops get no pickup run', function () {
    $shop = dispatchShop();
    $service = app(LogisticsService::class);

    $service->dispatchPickupRun(autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'pending']));
    $service->dispatchPickupRun(autoOrder($shop, ['status' => 'completed']));
    $service->dispatchPickupRun(autoOrder(dispatchShop(['offers_delivery' => false]), ['status' => 'pending']));

    expect(Delivery::count())->toBe(0);
});

test('a pickup run is created once and does not block the return delivery', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['status' => 'pending', 'deliver_to_customer' => true]);
    $service = app(LogisticsService::class);

    $service->dispatchPickupRun($order);
    $service->dispatchPickupRun($order);
    app(ShopOrderService::class)->updateStatus($order->fresh(), 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->where('type', 'pickup')->count())->toBe(1)
        ->and(Delivery::where('shop_order_id', $order->id)->where('type', 'delivery')->count())->toBe(1)
        ->and($order->fresh()->delivery->type)->toBe('delivery')
        ->and($order->fresh()->pickupRun->type)->toBe('pickup');
});

test('the owner sees the pickup run on the logistics board', function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    $shop = dispatchShop();
    $order = autoOrder($shop, ['status' => 'pending']);
    app(LogisticsService::class)->dispatchPickupRun($order);

    $this->actingAs($shop->owner)
        ->get('/shop/logistics')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/logistics/Index')
            ->where('deliveries.data.0.type', 'pickup')
            ->where('deliveries.data.0.customer_name', 'Maria Cruz')
            ->where('stats.pending', 1));
});

// ── Walk-in customers who want it delivered ──────────────────────────────────

/** Staff who may manage orders (Operations: manage), the same people who edit and complete them. */
function orderManager(Shop $shop, string $role = 'order-manager'): User
{
    $user = dispatcher($shop, [], $role);
    RolePermission::create(['shop_id' => $shop->id, 'role' => $role, 'module' => 'Operations', 'action' => 'manage']);

    return $user;
}

test('a walk-in order marked deliver to customer joins the board when it is completed', function () {
    $shop = dispatchShop();
    $rider = onlineRider($shop, 'Free');
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'deliver_to_customer' => true]);

    app(LogisticsService::class)->dispatchPickupRun($order);
    expect(Delivery::count())->toBe(0);

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->first())
        ->type->toBe('delivery')
        ->customer_name->toBe('Maria Cruz')
        ->delivery_address->toBe('12 Rizal St, Imus')
        ->rider_id->toBe($rider->id);
});

test('staff send a completed walk-in order for delivery in one click', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $rider = onlineRider($shop, 'Free');
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs($staff)
        ->post("/staff/operations/orders/{$order->payment_public_id}/send-for-delivery")
        ->assertSessionHasNoErrors();

    expect(Delivery::where('shop_order_id', $order->id)->first())
        ->type->toBe('delivery')
        ->customer_name->toBe('Maria Cruz')
        ->customer_phone->not->toBeNull()
        ->delivery_address->toBe('12 Rizal St, Imus')
        ->status->toBe('assigned')
        ->rider_id->toBe($rider->id);
});

test('an order with no address needs one and saves it on the order', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed', 'customer_address' => null]);

    $this->actingAs($staff)
        ->post("/staff/operations/orders/{$order->payment_public_id}/send-for-delivery")
        ->assertSessionHasErrors('delivery_address');
    expect(Delivery::count())->toBe(0);

    $this->actingAs($staff)
        ->post("/staff/operations/orders/{$order->payment_public_id}/send-for-delivery", ['delivery_address' => '5 Mabini St, Bacoor'])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->customer_address)->toBe('5 Mabini St, Bacoor')
        ->and(Delivery::where('shop_order_id', $order->id)->first()->delivery_address)->toBe('5 Mabini St, Bacoor');
});

test('only a completed order without a delivery can be sent', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $open = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'in_progress']);
    $done = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs($staff)->post("/staff/operations/orders/{$open->payment_public_id}/send-for-delivery")
        ->assertSessionHasErrors('delivery');

    $this->actingAs($staff)->post("/staff/operations/orders/{$done->payment_public_id}/send-for-delivery")->assertSessionHasNoErrors();
    $this->actingAs($staff)->post("/staff/operations/orders/{$done->payment_public_id}/send-for-delivery")
        ->assertSessionHasErrors('delivery');

    expect(Delivery::count())->toBe(1);
});

test('send for delivery is limited to the shops own staff, with the order permission and delivery on', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs(orderManager(dispatchShop(['shop_name' => 'Other Laundry'])))
        ->post("/staff/operations/orders/{$order->payment_public_id}/send-for-delivery")
        ->assertNotFound();

    $this->actingAs(dispatcher($shop, ['view', 'update'], 'no-order-access'))
        ->post("/staff/operations/orders/{$order->payment_public_id}/send-for-delivery")
        ->assertRedirect(route('staff.dashboard'));

    $noDelivery = dispatchShop(['offers_delivery' => false, 'shop_name' => 'No Delivery']);
    $this->actingAs(orderManager($noDelivery))
        ->post('/staff/operations/orders/'.autoOrder($noDelivery, ['status' => 'completed'])->payment_public_id.'/send-for-delivery')
        ->assertForbidden();

    expect(Delivery::count())->toBe(0);
});

test('the order page tells staff when delivery can be sent', function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    $shop = dispatchShop();
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs($shop->owner)
        ->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page
            ->where('deliveryOption.can_send', true)
            ->where('deliveryOption.has_address', true));

    app(LogisticsService::class)->sendOrderForDelivery($shop->owner, $order, null);

    $this->actingAs($shop->owner)
        ->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page
            ->where('deliveryOption.can_send', false)
            ->where('deliveryOption.delivery_status', 'pending'));
});

// ── Completing or changing an order through the Edit form ────────────────────

/** The payload the Edit form submits for an order, with the given fields changed. */
function editPayload(ShopOrder $order, array $changes = []): array
{
    return array_merge([
        'customer_name' => $order->customer_name,
        'customer_phone' => '09181234567',
        // Required whenever a rider is involved; ignored (and cleared) for a walk-in order.
        'alternate_contact_name' => 'Aling Nena',
        'alternate_contact_phone' => '09171234567',
        'customer_address' => $order->customer_address,
        'deliver_to_customer' => (bool) $order->deliver_to_customer,
        'service_id' => $order->service_id,
        'pickup_type' => $order->pickup_type,
        'pricing_model' => 'per_kg',
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'price_per_kg' => 60,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 180,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => $order->status,
    ], $changes);
}

// ── The Edit form: placed orders are locked ──────────────────────────────────

test('the Edit form cannot change the customer, status, fulfillment or service of an order', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'in_progress', 'actual_weight_kg' => 3]);
    $other = ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Dry Clean', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 200,
    ]);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, [
            'customer_name' => 'Someone Else',
            'customer_phone' => '09991234567',
            'status' => 'completed',
            'pickup_type' => 'walk_in',
            'deliver_to_customer' => true,
            'service_id' => $other->id,
        ]))
        ->assertSessionHasNoErrors();

    expect($order->fresh())
        ->customer_name->toBe('Maria Cruz')
        ->customer_phone->toBe($order->customer_phone)
        ->status->toBe('in_progress')
        ->pickup_type->toBe('pickup')
        ->deliver_to_customer->toBeFalse()
        ->service_id->toBe($order->service_id)
        ->and(Delivery::where('shop_order_id', $order->id)->where('type', 'delivery')->count())->toBe(0);
});

test('the Edit form updates where the rider goes on an order a rider handles', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'pending']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, [
            // The laundry is still with the customer, so nothing is weighed yet.
            'actual_weight_kg' => null,
            'customer_address' => '34 Mabini St, Imus',
            'alternate_contact_name' => 'Pedro Cruz',
            'alternate_contact_phone' => '09170000000',
        ]))
        ->assertSessionHasNoErrors();

    expect($order->fresh())
        ->customer_address->toBe('34 Mabini St, Imus')
        ->alternate_contact_name->toBe('Pedro Cruz')
        ->alternate_contact_phone->not->toBeNull();
});

test('an order a rider handles still needs an address in the Edit form', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'pending']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['customer_address' => '']))
        ->assertSessionHasErrors('customer_address');

    expect($order->fresh()->customer_address)->toBe('12 Rizal St, Imus');
});

test('a walk-in order without delivery keeps its details when edited', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'pending', 'actual_weight_kg' => 3]);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['customer_address' => 'Somewhere else']))
        ->assertSessionHasNoErrors();

    expect($order->fresh()->customer_address)->toBe('12 Rizal St, Imus');
});

test('a recorded weight cannot be changed in the Edit form', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'in_progress', 'actual_weight_kg' => 3]);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['actual_weight_kg' => 5]))
        ->assertSessionHasNoErrors();

    expect((float) $order->fresh()->actual_weight_kg)->toBe(3.0)
        ->and((float) $order->fresh()->total_amount)->toBe(180.0);
});

// ── Pickup orders: weigh only after the rider brings the laundry in ──────────

function pickupRunFor(ShopOrder $order, string $status): Delivery
{
    return Delivery::create([
        'shop_id' => $order->shop_id,
        'shop_order_id' => $order->id,
        'type' => 'pickup',
        'customer_name' => $order->customer_name,
        'status' => $status,
    ]);
}

test('a customer drop-off closes the pickup, unlocks weighing and sends no rider later', function () {
    Notification::fake();
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $order = autoOrder($shop, ['status' => 'pending']);
    $run = pickupRunFor($order, 'pending');
    $service = app(App\Services\LogisticsService::class);

    expect($service->weighingBlockedReason($order))->not->toBeNull();

    $this->actingAs($staff)
        ->post("/staff/logistics/{$run->public_id}/dropped-off")
        ->assertSessionHasNoErrors()
        ->assertSessionHas('toast.type', 'success');

    expect($run->fresh())
        ->status->toBe('delivered')
        ->delivered_at->not->toBeNull()
        ->picked_up_at->toBeNull()
        ->rider_id->toBeNull()
        ->and($service->weighingBlockedReason($order))->toBeNull();

    // A rider who comes free afterwards is not sent for laundry already at the shop.
    onlineRider($shop, 'Free');
    expect($service->assignWaitingDeliveries($shop->id))->toBe(0)
        ->and($run->fresh()->rider_id)->toBeNull();

    Notification::assertSentTo($order->customer, App\Notifications\DeliveryNotification::class, function ($notification) use ($order) {
        return $notification->toDatabase($order->customer)['title'] === 'Laundry received at the shop';
    });
});

test('after a drop-off the customer is no longer told to drop off', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['status' => 'pending']);
    $run = pickupRunFor($order, 'pending');
    $tracking = app(App\Services\DeliveryTrackingService::class);

    expect($tracking->noRiderNotice($order))->toContain('Please drop off your laundry');

    app(App\Services\LogisticsService::class)->markDroppedOff($shop->owner, $run);

    expect($tracking->noRiderNotice($order->fresh()))->toBeNull();
});

test('only a pickup still waiting for a rider can be marked as dropped off', function (string $type, string $status, bool $hasRider, string $courier) {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $order = autoOrder($shop, ['status' => 'pending']);
    $rider = onlineRider($shop, 'Busy');
    $run = Delivery::create([
        'shop_id' => $shop->id, 'shop_order_id' => $order->id, 'type' => $type, 'customer_name' => 'Maria Cruz',
        'status' => $status, 'rider_id' => $hasRider ? $rider->id : null, 'courier' => $courier,
    ]);

    $this->actingAs($staff)
        ->post("/staff/logistics/{$run->public_id}/dropped-off")
        ->assertSessionHasErrors('delivery');

    expect($run->fresh()->status)->toBe($status)->and($run->fresh()->delivered_at)->toBeNull();
})->with([
    'a delivery run' => ['delivery', 'pending', false, 'own_rider'],
    'a rider already has it' => ['pickup', 'assigned', true, 'own_rider'],
    'already collected' => ['pickup', 'picked_up', true, 'own_rider'],
    'already received' => ['pickup', 'delivered', false, 'own_rider'],
    'a courier has it' => ['pickup', 'pending', false, 'lalamove'],
]);

test('marking a drop-off needs the update permission and the same shop', function () {
    $shop = dispatchShop();
    $run = pickupRunFor(autoOrder($shop, ['status' => 'pending']), 'pending');

    $this->actingAs(dispatcher($shop, ['view'], 'viewer'))
        ->post("/staff/logistics/{$run->public_id}/dropped-off")
        ->assertRedirect(route('staff.dashboard'));
    expect($run->fresh()->status)->toBe('pending');

    $other = dispatchShop();
    $this->actingAs(dispatcher($other, ['view', 'update'], 'outsider'))
        ->post("/staff/logistics/{$run->public_id}/dropped-off")
        ->assertForbidden();
    expect($run->fresh()->status)->toBe('pending');
});

test('a pickup order that did not ask for delivery gets no return delivery when completed', function () {
    $shop = dispatchShop();
    onlineRider($shop, 'Free');
    $order = autoOrder($shop, ['actual_weight_kg' => 3]);

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->where('type', 'delivery')->count())->toBe(0);
});

test('a pickup order cannot be weighed until its pickup run is delivered', function (string $status) {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'pending']);
    pickupRunFor($order, $status);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['actual_weight_kg' => 3]))
        ->assertSessionHasErrors('actual_weight_kg');

    expect($order->fresh()->actual_weight_kg)->toBeNull();
})->with(['pending', 'assigned', 'picked_up', 'failed']);

test('a pickup order can be weighed once the rider has delivered the laundry to the shop', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'pending']);
    pickupRunFor($order, 'delivered');
    Storage::fake('private');

    // An online customer approves the weight, so the scale photo goes with it.
    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, [
            'actual_weight_kg' => 3,
            'weight_proof' => UploadedFile::fake()->image('scale.jpg'),
        ]))
        ->assertSessionHasNoErrors();

    expect((float) $order->fresh()->actual_weight_kg)->toBe(3.0)
        ->and($order->fresh()->weight_approval_status)->toBe('pending');
});

test('an online order cannot be weighed in the Edit form without a photo of the scale', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'pending']);
    pickupRunFor($order, 'delivered');

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['actual_weight_kg' => 3]))
        ->assertSessionHasErrors('weight_proof');

    expect($order->fresh()->actual_weight_kg)->toBeNull();
});

test('a walk-in order is weighed once in the Edit form and priced at its stored rate', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, [
        'pickup_type' => 'walk_in', 'order_source' => 'shop', 'status' => 'pending',
        'estimated_weight_kg' => null, 'total_amount' => 0,
    ]);
    // A later price change never reprices a placed order.
    ShopService::whereKey($order->service_id)->update(['price_per_kg' => 999]);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['actual_weight_kg' => 4]))
        ->assertSessionHasNoErrors();

    expect((float) $order->fresh()->actual_weight_kg)->toBe(4.0)
        ->and((float) $order->fresh()->total_amount)->toBe(240.0);
});

test('a pickup order with no pickup run yet cannot be weighed either', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['status' => 'pending']);

    expect(app(ShopOrderService::class)->weighingBlockedReason($order))->toContain('pickup');
});

test('saving a pickup order without changing its weight is not blocked', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['actual_weight_kg' => 3, 'status' => 'in_progress']);
    pickupRunFor($order, 'assigned');

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, ['customer_address' => '34 Mabini St, Imus']))
        ->assertSessionHasNoErrors();

    expect($order->fresh()->customer_address)->toBe('34 Mabini St, Imus');
});

test('walk-in orders and shops without delivery can be weighed straight away', function () {
    $shop = dispatchShop();
    $walkIn = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'pending']);
    $noDelivery = autoOrder(dispatchShop(['offers_delivery' => false]), ['status' => 'pending']);

    expect(app(ShopOrderService::class)->weighingBlockedReason($walkIn))->toBeNull()
        ->and(app(ShopOrderService::class)->weighingBlockedReason($noDelivery))->toBeNull();
});

test('a new pickup order cannot be created already weighed', function () {
    $shop = dispatchShop();

    expect(fn () => app(ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id,
        'pickup_type' => 'pickup',
        'actual_weight_kg' => 3,
        'customer_agreement_version' => '1.0',
        'total_amount' => 180,
    ], $shop->owner))->toThrow(Illuminate\Validation\ValidationException::class);

    expect(ShopOrder::count())->toBe(0);
});

test('the Edit page tells staff why a pickup order cannot be weighed yet', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'pending']);
    pickupRunFor($order, 'picked_up');

    $this->actingAs($staff)
        ->get("/staff/operations/orders/{$order->payment_public_id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('weighingBlockedReason', fn ($reason) => str_contains($reason, 'pickup')));

    Delivery::where('shop_order_id', $order->id)->update(['status' => 'delivered']);

    $this->actingAs($staff)
        ->get("/staff/operations/orders/{$order->payment_public_id}/edit")
        ->assertInertia(fn ($page) => $page->where('weighingBlockedReason', null));
});

test('the order page tracks the delivery back, never the pickup run', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['status' => 'completed']);
    pickupRunFor($order, 'delivered');

    $this->actingAs($order->customer)
        ->getJson(route('user.orders.tracking', $order->payment_public_id))
        ->assertNotFound();

    Delivery::create([
        'shop_id' => $shop->id,
        'shop_order_id' => $order->id,
        'type' => 'delivery',
        'customer_name' => 'Maria Cruz',
        'status' => 'assigned',
    ]);

    $this->actingAs($order->customer)
        ->getJson(route('user.orders.tracking', $order->payment_public_id))
        ->assertOk()
        ->assertJsonPath('status', 'assigned');
});

// ── Payment and pricing are not changed from the Edit form ───────────────────

test('the Edit form cannot mark an order paid or change its charges', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed', 'actual_weight_kg' => 3, 'payment_method' => 'cash']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", editPayload($order, [
            'payment_state' => 'paid',
            'payment_method' => 'gcash',
            'additional_charges' => 20,
            'discount_amount' => 10,
            'total_amount' => 999,
        ]))
        ->assertSessionHasNoErrors();

    expect($order->fresh())
        ->payment_status->toBe('unpaid')
        ->payment_method->toBe('cash')
        ->and((float) $order->fresh()->amount_paid)->toBe(0.0)
        ->and((float) $order->fresh()->additional_charges)->toBe(0.0)
        ->and((float) $order->fresh()->total_amount)->toBe(180.0)
        ->and(App\Models\ShopOrderPayment::where('shop_order_id', $order->id)->count())->toBe(0);
});
