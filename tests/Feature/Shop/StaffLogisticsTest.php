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
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Rico', 'status' => 'active']);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->id}/status", ['status' => 'assigned', 'rider_id' => $rider->id])
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
        ->patch("/staff/logistics/{$delivery->id}/status", ['status' => 'delivered'])
        ->assertRedirect(route('staff.dashboard'));

    expect($delivery->fresh()->status)->toBe('pending');
});

test('view-only staff cannot assign riders or remove deliveries', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view']);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)->get('/staff/logistics')->assertOk();
    $this->actingAs($staff)->patch("/staff/logistics/{$delivery->id}/status", ['status' => 'assigned'])
        ->assertRedirect(route('staff.dashboard'));
    $this->actingAs($staff)->delete("/staff/logistics/{$delivery->id}")
        ->assertRedirect(route('staff.dashboard'));

    expect(Delivery::find($delivery->id))->not->toBeNull();
});

test('staff cannot reach another shop\'s deliveries or assign its riders', function () {
    $shop = dispatchShop();
    $other = dispatchShop(['shop_name' => 'Other Laundry']);
    $staff = dispatcher($shop, ['view', 'update']);
    $foreignDelivery = Delivery::create(['shop_id' => $other->id, 'customer_name' => 'Zed', 'status' => 'pending']);
    $foreignRider = Rider::create(['shop_id' => $other->id, 'name' => 'Rex', 'status' => 'active']);
    $ownDelivery = Delivery::create(['shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'pending']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$foreignDelivery->id}/status", ['status' => 'assigned'])
        ->assertForbidden();

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$ownDelivery->id}/status", ['status' => 'assigned', 'rider_id' => $foreignRider->id])
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

test('completing a pickup order puts it on the board with the customer details and a free rider', function () {
    $shop = dispatchShop();
    $busy = Rider::create(['shop_id' => $shop->id, 'name' => 'Busy', 'status' => 'active']);
    Delivery::create(['shop_id' => $shop->id, 'rider_id' => $busy->id, 'customer_name' => 'X', 'status' => 'picked_up']);
    $free = Rider::create(['shop_id' => $shop->id, 'name' => 'Free', 'status' => 'active']);
    $order = autoOrder($shop);

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
    $busy = Rider::create(['shop_id' => $shop->id, 'name' => 'Busy', 'status' => 'active']);
    Delivery::create(['shop_id' => $shop->id, 'rider_id' => $busy->id, 'customer_name' => 'X', 'status' => 'assigned']);
    $order = autoOrder($shop);

    app(ShopOrderService::class)->updateStatus($order, 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->first())
        ->status->toBe('pending')
        ->rider_id->toBeNull();
});

test('inactive riders are never auto assigned', function () {
    $shop = dispatchShop();
    Rider::create(['shop_id' => $shop->id, 'name' => 'Off', 'status' => 'inactive']);
    $order = autoOrder($shop);

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
    $order = autoOrder($shop);

    app(ShopOrderService::class)->updateStatus($order, 'completed');
    app(ShopOrderService::class)->updateStatus($order->fresh(), 'completed');

    expect(Delivery::where('shop_order_id', $order->id)->count())->toBe(1);
});

// ── Lalamove bookings ────────────────────────────────────────────────────────

test('a delivery can be handed to Lalamove with the booking details and no shop rider', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Rico', 'status' => 'active']);
    $delivery = Delivery::create(['shop_id' => $shop->id, 'rider_id' => $rider->id, 'customer_name' => 'Ana', 'status' => 'assigned']);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->id}/status", [
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
        ->patch("/staff/logistics/{$delivery->id}/status", ['status' => 'assigned', 'courier' => 'lalamove'])
        ->assertSessionHasErrors('courier_reference');

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->id}/status", [
            'status' => 'pending',
            'courier' => 'lalamove',
            'courier_tracking_url' => 'http://insecure.example/track',
        ])
        ->assertSessionHasErrors('courier_tracking_url');

    expect($delivery->fresh()->courier)->toBe('own_rider');
});

test('going back to an own rider clears the courier details', function () {
    $shop = dispatchShop();
    $staff = dispatcher($shop, ['view', 'update']);
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Rico', 'status' => 'active']);
    $delivery = Delivery::create([
        'shop_id' => $shop->id, 'customer_name' => 'Ana', 'status' => 'assigned',
        'courier' => 'lalamove', 'courier_reference' => 'LLM-1', 'courier_cost' => 90,
    ]);

    $this->actingAs($staff)
        ->patch("/staff/logistics/{$delivery->id}/status", [
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
        ->post("/staff/logistics/{$delivery->id}/photos", [
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
    $this->actingAs($staff)->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('a.png')]]);
    $photo = $delivery->photos()->first();

    $otherCustomer = User::factory()->create(['role' => AccountType::Customer->value]);
    $otherStaff = dispatcher(dispatchShop(['shop_name' => 'Other Laundry']), ['view', 'update']);

    $this->actingAs($otherCustomer)->get("/user/orders/delivery-photos/{$photo->public_id}")->assertNotFound();
    $this->actingAs($otherStaff)->get("/staff/logistics/photos/{$photo->public_id}")->assertNotFound();
    $this->actingAs($otherStaff)
        ->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('b.png')]])
        ->assertForbidden();
});

test('proof uploads accept only small images, a few at a time', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update']);

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')]])
        ->assertSessionHasErrors('photos.0');

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('big.png', 6000)]])
        ->assertSessionHasErrors('photos.0');

    foreach (range(1, 2) as $i) {
        $this->actingAs($staff)->post("/staff/logistics/{$delivery->id}/photos", ['photos' => array_map(
            fn ($n) => proofImage("p{$i}{$n}.png"), range(1, 4),
        )])->assertSessionHasNoErrors();
    }

    $this->actingAs($staff)
        ->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('extra.png')]])
        ->assertSessionHasErrors('photos');

    expect($delivery->photos()->count())->toBe(8);
});

test('only staff with the update permission upload and only with delete permission remove proof', function () {
    [$shop, , $delivery] = proofSetup();
    $viewer = dispatcher($shop, ['view']);

    $this->actingAs($viewer)
        ->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('a.png')]])
        ->assertRedirect(route('staff.dashboard'));
    expect($delivery->photos()->count())->toBe(0);

    $uploader = dispatcher($shop, ['view', 'update'], 'uploader');
    $this->actingAs($uploader)->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('a.png')]]);
    $photo = $delivery->photos()->first();

    $this->actingAs($uploader)->delete("/staff/logistics/photos/{$photo->public_id}")->assertRedirect(route('staff.dashboard'));
    expect($delivery->photos()->count())->toBe(1);

    $remover = dispatcher($shop, ['view', 'archive'], 'remover');
    $this->actingAs($remover)->delete("/staff/logistics/photos/{$photo->public_id}")->assertSessionHasNoErrors();

    expect($delivery->photos()->count())->toBe(0);
    Storage::disk('private')->assertMissing($photo->path);
});

test('removing a delivery removes its proof files', function () {
    [$shop, , $delivery] = proofSetup();
    $staff = dispatcher($shop, ['view', 'update', 'archive']);
    $this->actingAs($staff)->post("/staff/logistics/{$delivery->id}/photos", ['photos' => [proofImage('a.png')]]);
    $path = $delivery->photos()->first()->path;

    $this->actingAs($staff)->delete("/staff/logistics/{$delivery->id}")->assertSessionHasNoErrors();

    Storage::disk('private')->assertMissing($path);
});

// ── Pickup runs (collecting the laundry from the customer) ───────────────────

test('a new pickup order gets a pickup run with the customer details and a free rider', function () {
    $shop = dispatchShop();
    $busy = Rider::create(['shop_id' => $shop->id, 'name' => 'Busy', 'status' => 'active']);
    Delivery::create(['shop_id' => $shop->id, 'rider_id' => $busy->id, 'customer_name' => 'X', 'status' => 'assigned']);
    $free = Rider::create(['shop_id' => $shop->id, 'name' => 'Free', 'status' => 'active']);
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
    $order = autoOrder($shop, ['status' => 'pending']);
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
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Free', 'status' => 'active']);
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
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Free', 'status' => 'active']);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs($staff)
        ->post("/staff/operations/orders/{$order->id}/send-for-delivery")
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
        ->post("/staff/operations/orders/{$order->id}/send-for-delivery")
        ->assertSessionHasErrors('delivery_address');
    expect(Delivery::count())->toBe(0);

    $this->actingAs($staff)
        ->post("/staff/operations/orders/{$order->id}/send-for-delivery", ['delivery_address' => '5 Mabini St, Bacoor'])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->customer_address)->toBe('5 Mabini St, Bacoor')
        ->and(Delivery::where('shop_order_id', $order->id)->first()->delivery_address)->toBe('5 Mabini St, Bacoor');
});

test('only a completed order without a delivery can be sent', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $open = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'in_progress']);
    $done = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs($staff)->post("/staff/operations/orders/{$open->id}/send-for-delivery")
        ->assertSessionHasErrors('delivery');

    $this->actingAs($staff)->post("/staff/operations/orders/{$done->id}/send-for-delivery")->assertSessionHasNoErrors();
    $this->actingAs($staff)->post("/staff/operations/orders/{$done->id}/send-for-delivery")
        ->assertSessionHasErrors('delivery');

    expect(Delivery::count())->toBe(1);
});

test('send for delivery is limited to the shops own staff, with the order permission and delivery on', function () {
    $shop = dispatchShop();
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'completed']);

    $this->actingAs(orderManager(dispatchShop(['shop_name' => 'Other Laundry'])))
        ->post("/staff/operations/orders/{$order->id}/send-for-delivery")
        ->assertForbidden();

    $this->actingAs(dispatcher($shop, ['view', 'update'], 'no-order-access'))
        ->post("/staff/operations/orders/{$order->id}/send-for-delivery")
        ->assertRedirect(route('staff.dashboard'));

    $noDelivery = dispatchShop(['offers_delivery' => false, 'shop_name' => 'No Delivery']);
    $this->actingAs(orderManager($noDelivery))
        ->post('/staff/operations/orders/'.autoOrder($noDelivery, ['status' => 'completed'])->id.'/send-for-delivery')
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
        ->get("/shop/operations/orders/{$order->id}")
        ->assertInertia(fn ($page) => $page
            ->where('deliveryOption.can_send', true)
            ->where('deliveryOption.has_address', true));

    app(LogisticsService::class)->sendOrderForDelivery($shop->owner, $order, null);

    $this->actingAs($shop->owner)
        ->get("/shop/operations/orders/{$order->id}")
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

test('completing a pickup order in the Edit form creates its return delivery', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $pickupRider = Rider::create(['shop_id' => $shop->id, 'name' => 'Collector', 'status' => 'active']);
    $order = autoOrder($shop, ['status' => 'in_progress']);
    app(LogisticsService::class)->dispatchPickupRun($order);
    // The first rider is out collecting, so a second rider takes the return trip.
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Free', 'status' => 'active']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->id}", editPayload($order, ['status' => 'completed']))
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('completed')
        ->and(Delivery::where('shop_order_id', $order->id)->where('type', 'pickup')->count())->toBe(1)
        ->and(Delivery::where('shop_order_id', $order->id)->where('type', 'delivery')->first())
        ->customer_name->toBe('Maria Cruz')
        ->delivery_address->toBe('12 Rizal St, Imus')
        ->rider_id->toBe($rider->id);
});

test('completing a walk-in order marked for delivery in the Edit form creates the delivery', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'in_progress']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->id}", editPayload($order, [
            'status' => 'completed',
            'deliver_to_customer' => true,
        ]))
        ->assertSessionHasNoErrors();

    expect($order->fresh()->deliver_to_customer)->toBeTrue()
        ->and(Delivery::where('shop_order_id', $order->id)->where('type', 'delivery')->count())->toBe(1)
        ->and(Delivery::where('type', 'pickup')->count())->toBe(0);
});

test('switching an open order to pickup in the Edit form creates the pickup run', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'pending']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->id}", editPayload($order, ['pickup_type' => 'pickup']))
        ->assertSessionHasNoErrors();

    expect(Delivery::where('shop_order_id', $order->id)->first())
        ->type->toBe('pickup')
        ->customer_name->toBe('Maria Cruz');
});

test('the Edit form needs an address to deliver to and a shop that delivers', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'in_progress', 'customer_address' => null]);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->id}", editPayload($order, ['deliver_to_customer' => true]))
        ->assertSessionHasErrors('customer_address');

    $noDelivery = dispatchShop(['offers_delivery' => false, 'shop_name' => 'No Delivery']);
    $other = autoOrder($noDelivery, ['pickup_type' => 'walk_in', 'status' => 'in_progress']);
    $this->actingAs(orderManager($noDelivery))
        ->put("/staff/operations/orders/{$other->id}", editPayload($other, ['deliver_to_customer' => true]))
        ->assertSessionHasErrors('deliver_to_customer');

    expect($order->fresh()->deliver_to_customer)->toBeFalse()
        ->and(Delivery::count())->toBe(0);
});

test('saving an already completed order again does not create another delivery', function () {
    $shop = dispatchShop();
    $staff = orderManager($shop);
    $order = autoOrder($shop, ['status' => 'in_progress']);

    $payload = editPayload($order, ['status' => 'completed']);
    $this->actingAs($staff)->put("/staff/operations/orders/{$order->id}", $payload)->assertSessionHasNoErrors();
    $this->actingAs($staff)->put("/staff/operations/orders/{$order->id}", $payload)->assertSessionHasNoErrors();

    expect(Delivery::where('shop_order_id', $order->id)->where('type', 'delivery')->count())->toBe(1);
});
