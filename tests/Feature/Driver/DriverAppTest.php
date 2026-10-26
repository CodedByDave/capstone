<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\DriverRosterService;
use App\Services\LogisticsService;
use Inertia\Testing\AssertableInertia as Assert;

function driverRun(Shop $shop, ?Rider $rider, string $status = 'assigned', array $overrides = []): Delivery
{
    return Delivery::create(array_merge([
        'shop_id' => $shop->id,
        'type' => 'delivery',
        'customer_name' => 'Andrea Santos',
        'customer_phone' => '09181234567',
        'delivery_address' => 'Unit 4B, Palm Residences',
        'destination_lat' => 14.4,
        'destination_lng' => 120.9,
        'status' => $status,
        'rider_id' => $rider?->id,
        'assigned_at' => $rider ? now() : null,
    ], $overrides));
}

// ── Who may enter ─────────────────────────────────────────────────────────────

test('a driver sees their own dashboard', function () {
    $shop = driverShop();
    $rider = onlineRider($shop, 'Marco');
    driverRun($shop, $rider);

    $this->actingAs($rider->employee->user)
        ->get('/driver')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('driver/Home')
            ->where('driver.name', 'Marco Driver')
            ->where('driver.is_online', true)
            ->has('next_stop')
            ->where('queue.deliveries.0.customer_name', 'Andrea Santos'));
});

test('a staff member who is not a driver gets a 404 from the driver app', function () {
    $shop = driverShop();
    $user = User::factory()->create(['role' => AccountType::Staff->value, 'shop_id' => $shop->id]);
    Employee::create([
        'shop_id' => $shop->id, 'user_id' => $user->id, 'employee_id' => 'CSH-1',
        'first_name' => 'Cash', 'last_name' => 'Ier', 'position' => 'Cashier',
        'hire_date' => today(), 'status' => 'Active',
    ]);

    $this->actingAs($user)->get('/driver')->assertNotFound();
});

test('customers and guests cannot open the driver app', function () {
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->get('/driver')->assertRedirect();
    $this->actingAs($customer)->get('/driver')->assertRedirect();
});

test('a driver who opens the staff dashboard is sent to the driver app', function () {
    $rider = onlineRider(driverShop());

    $this->actingAs($rider->employee->user)
        ->get('/staff/dashboard')
        ->assertRedirect('/driver');
});

test('the driver manifest installs the app on its own scope', function () {
    $this->get('/driver/manifest.webmanifest')
        ->assertOk()
        ->assertJsonPath('scope', '/driver')
        ->assertJsonPath('start_url', '/driver');
});

// ── Own jobs only ─────────────────────────────────────────────────────────────

test('a driver cannot open or change another driver\'s run', function () {
    $shop = driverShop();
    $mine = onlineRider($shop, 'Mine');
    $theirs = onlineRider($shop, 'Theirs');
    $run = driverRun($shop, $theirs);

    $this->actingAs($mine->employee->user)->get("/driver/jobs/{$run->public_id}")->assertNotFound();
    $this->actingAs($mine->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up'])
        ->assertNotFound();

    expect($run->fresh()->status)->toBe('assigned');
});

test('a driver cannot reach a run of another shop', function () {
    $mine = onlineRider(driverShop(), 'Mine');
    $otherShop = driverShop();
    $run = driverRun($otherShop, onlineRider($otherShop, 'Other'));

    $this->actingAs($mine->employee->user)->get("/driver/jobs/{$run->public_id}")->assertNotFound();
});

// ── The same flow: assigned, picked up, delivered ─────────────────────────────

test('a run moves forward through pickup to delivered and notifies nobody backwards', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider);
    $driver = $rider->employee->user;

    $this->actingAs($driver)->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up'])->assertRedirect();
    expect($run->fresh()->status)->toBe('picked_up')->and($run->fresh()->picked_up_at)->not->toBeNull();

    $this->actingAs($driver)->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'delivered'])->assertRedirect();
    expect($run->fresh()->status)->toBe('delivered')->and($run->fresh()->delivered_at)->not->toBeNull();

    // A finished run cannot be pushed back.
    $this->actingAs($driver)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'picked_up'])
        ->assertSessionHas('toast.type', 'error');
    expect($run->fresh()->status)->toBe('delivered');
});

test('a driver can report an issue, which fails the run with their note', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider);

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'failed', 'notes' => 'Nobody home'])
        ->assertRedirect();

    expect($run->fresh()->status)->toBe('failed')->and($run->fresh()->notes)->toBe('Nobody home');
});

test('the status must be one a driver may set', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider);

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$run->public_id}/status", ['status' => 'pending'])
        ->assertSessionHasErrors('status');
});

test('finishing a run puts the driver back in line and gives them the oldest waiting order', function () {
    $shop = driverShop();
    $rider = onlineRider($shop, freeSince: now()->subHour());
    $onRoad = driverRun($shop, $rider, 'picked_up');
    $waiting = driverRun($shop, null, 'pending');

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$onRoad->public_id}/status", ['status' => 'delivered']);

    expect($waiting->fresh()->rider_id)->toBe($rider->id)
        ->and($waiting->fresh()->status)->toBe('assigned')
        ->and($rider->fresh()->available_since->gt(now()->subMinute()))->toBeTrue();
});

// ── Online switch ─────────────────────────────────────────────────────────────

test('going online takes an order that was waiting for a driver', function () {
    $shop = driverShop();
    $rider = onlineRider($shop, online: false);
    $waiting = driverRun($shop, null, 'pending');

    $this->actingAs($rider->employee->user)
        ->patch('/driver/availability', ['online' => true])
        ->assertRedirect();

    expect($rider->fresh()->is_online)->toBeTrue()
        ->and($waiting->fresh()->rider_id)->toBe($rider->id);
});

test('a driver without a shift today cannot go online', function () {
    $rider = onlineRider(driverShop(), online: false, scheduled: false);

    $this->actingAs($rider->employee->user)
        ->patch('/driver/availability', ['online' => true])
        ->assertRedirect();

    expect($rider->fresh()->is_online)->toBeFalse();
});

test('an order is never given to a driver without a shift today', function () {
    $shop = driverShop();
    onlineRider($shop, scheduled: false);
    $waiting = driverRun($shop, null, 'pending');

    expect(app(App\Repositories\RiderRepository::class)->findAvailableForShop($shop->id))->toBeNull()
        ->and($waiting->fresh()->rider_id)->toBeNull();
});

test('going offline stops new orders and clears the waiting time', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);

    $this->actingAs($rider->employee->user)->patch('/driver/availability', ['online' => false]);

    expect($rider->fresh()->is_online)->toBeFalse()->and($rider->fresh()->available_since)->toBeNull();
    $waiting = driverRun($shop, null, 'pending');
    expect(app(LogisticsService::class)->assignWaitingDeliveries($shop->id))->toBe(0)
        ->and($waiting->fresh()->status)->toBe('pending');
});

// ── Live location ─────────────────────────────────────────────────────────────

test('a driver shares their position only while the run is on the road', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider);
    $done = driverRun($shop, $rider, 'delivered');

    $this->actingAs($rider->employee->user)
        ->postJson("/driver/jobs/{$run->public_id}/location", ['lat' => 14.41, 'lng' => 120.91])
        ->assertOk();
    expect($run->fresh()->rider_lat)->toBe(14.41);

    $this->actingAs($rider->employee->user)
        ->postJson("/driver/jobs/{$done->public_id}/location", ['lat' => 14.41, 'lng' => 120.91])
        ->assertStatus(409);
});

test('a position outside the world is rejected', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider);

    $this->actingAs($rider->employee->user)
        ->postJson("/driver/jobs/{$run->public_id}/location", ['lat' => 120, 'lng' => 10])
        ->assertStatus(422);
});

// ── Next stop for the map ─────────────────────────────────────────────────────

test('a delivery run heads to the shop first and to the customer after pickup', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider, 'assigned');
    $user = $rider->employee->user;

    $this->actingAs($user)->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('job.target.kind', 'shop')
            ->where('job.action_label', 'Confirm pickup from shop')
            // Both stops are always sent so the driver can navigate to either one.
            ->where('job.customer_stop.kind', 'customer')
            ->where('job.customer_stop.address', 'Unit 4B, Palm Residences')
            ->where('job.shop_stop.kind', 'shop')
            ->where('job.shop_stop.lat', 14.43));

    $run->update(['status' => 'picked_up']);

    $this->actingAs($user)->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('job.target.kind', 'customer')
            ->where('job.action_label', 'Confirm delivery'));
});

test('a pickup run heads to the customer first and then to the shop', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $run = driverRun($shop, $rider, 'assigned', ['type' => 'pickup']);
    $user = $rider->employee->user;

    $this->actingAs($user)->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('job.target.kind', 'customer')
            ->where('job.action_label', 'Confirm pickup'));

    $run->update(['status' => 'picked_up']);

    $this->actingAs($user)->get("/driver/jobs/{$run->public_id}")
        ->assertInertia(fn (Assert $page) => $page->where('job.target.kind', 'shop'));
});

// ── Earnings follow the shop's choice ─────────────────────────────────────────

function deliveredToday(Shop $shop, Rider $rider, float $fee, string $type = 'delivery'): Delivery
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    $order = ShopOrder::create([
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'order_number' => 'LD-'.random_int(1000, 99999),
        'customer_name' => 'Andrea',
        'delivery_fee' => $fee,
        'total_amount' => 200,
        'status' => 'completed',
    ]);

    return driverRun($shop, $rider, 'delivered', [
        'shop_order_id' => $order->id,
        'type' => $type,
        'delivered_at' => now(),
    ]);
}

test('earnings are the delivery fee of completed deliveries when the shop pays by fee', function () {
    $shop = driverShop(['driver_pay_mode' => 'delivery_fee']);
    $rider = onlineRider($shop);
    deliveredToday($shop, $rider, 60);
    deliveredToday($shop, $rider, 40);
    deliveredToday($shop, $rider, 30, 'pickup');

    $this->actingAs($rider->employee->user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page
            ->where('today.earnings.enabled', true)
            ->where('today.earnings.amount', 100)
            ->where('today.jobs_completed', 3));
});

test('earnings are a flat daily rate once a run is completed', function () {
    $shop = driverShop(['driver_pay_mode' => 'daily_rate', 'driver_daily_rate' => 500]);
    $rider = onlineRider($shop);

    $this->actingAs($rider->employee->user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page->where('today.earnings.amount', 0));

    deliveredToday($shop, $rider, 60);
    deliveredToday($shop, $rider, 40);

    $this->actingAs($rider->employee->user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page->where('today.earnings.amount', 500));
});

test('earnings are hidden when the shop does not show them', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);

    $this->actingAs($rider->employee->user)->get('/driver/earnings')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('driver/Earnings')
            ->where('earnings.enabled', false)
            ->where('earnings.total', null));
});

// ── Employees become drivers ──────────────────────────────────────────────────

test('only the position Driver, in any capitalization, makes an employee a driver', function () {
    expect(DriverRosterService::isDriverPosition('Driver'))->toBeTrue()
        ->and(DriverRosterService::isDriverPosition('  driver '))->toBeTrue()
        ->and(DriverRosterService::isDriverPosition('DRIVER'))->toBeTrue()
        ->and(DriverRosterService::isDriverPosition('Rider'))->toBeTrue()
        ->and(DriverRosterService::isDriverPosition('Truck Driver'))->toBeFalse()
        ->and(DriverRosterService::isDriverPosition(null))->toBeFalse();
});

test('changing a driver\'s position or archiving them removes them from the drivers', function () {
    $shop = driverShop();
    $rider = onlineRider($shop);
    $employee = $rider->employee;
    $roster = app(DriverRosterService::class);

    $employee->update(['position' => 'Cashier']);
    $roster->sync($employee);
    expect($rider->fresh()->status)->toBe('inactive')->and($rider->fresh()->is_online)->toBeFalse();

    $employee->update(['position' => 'driver']);
    $roster->sync($employee);
    expect($rider->fresh()->status)->toBe('active');

    // Archiving records who did it.
    $this->actingAs(User::find($shop->owner_id));
    $employee->delete();
    $roster->sync($employee);
    expect($rider->fresh()->status)->toBe('inactive');
});

test('hiring a driver through the employee service adds them to the drivers', function () {
    $shop = driverShop();
    $this->actingAs($shop->owner ?? User::find($shop->owner_id));

    $employee = app(App\Services\EmployeeService::class)->createEmployee($shop, [
        'first_name' => 'Marco',
        'last_name' => 'Reyes',
        'email' => 'marco.driver@example.test',
        'phone' => '09171230000',
        'position' => 'Driver',
        'employment_type' => 'full_time',
        'hire_date' => today()->toDateString(),
        'employee_id' => 'EMP-DRV-1',
        'status' => 'Active',
    ]);

    $rider = Rider::where('employee_id', $employee->id)->first();

    expect($rider)->not->toBeNull()
        ->and($rider->shop_id)->toBe($shop->id)
        ->and($rider->name)->toBe('Marco Reyes')
        ->and($rider->is_online)->toBeFalse();
});

// ── Shop operating hours ──────────────────────────────────────────────────────

test('a shop knows whether it is open, including hours that pass midnight', function () {
    $always = new App\Models\Shop(['is_open_24_7' => true]);
    $day = new App\Models\Shop(['is_open_24_7' => false, 'opens_at' => '08:00:00', 'closes_at' => '17:00:00']);
    $night = new App\Models\Shop(['is_open_24_7' => false, 'opens_at' => '22:00:00', 'closes_at' => '06:00:00']);

    expect($always->isOpenAt(now()->setTime(3, 0)))->toBeTrue()
        ->and($day->isOpenAt(now()->setTime(9, 0)))->toBeTrue()
        ->and($day->isOpenAt(now()->setTime(17, 0)))->toBeFalse()
        ->and($day->isOpenAt(now()->setTime(2, 0)))->toBeFalse()
        ->and($night->isOpenAt(now()->setTime(23, 0)))->toBeTrue()
        ->and($night->isOpenAt(now()->setTime(5, 0)))->toBeTrue()
        ->and($night->isOpenAt(now()->setTime(12, 0)))->toBeFalse();
});

test('a closed shop does not hand waiting orders to a driver', function () {
    $shop = driverShop(['is_open_24_7' => false, 'opens_at' => '00:00:00', 'closes_at' => '00:01:00']);
    $this->travelTo(now()->setTime(12, 0));
    onlineRider($shop);
    $waiting = driverRun($shop, null, 'pending');

    expect(app(App\Services\LogisticsService::class)->assignWaitingDeliveries($shop->id))->toBe(0)
        ->and($waiting->fresh()->rider_id)->toBeNull();
});

test('the owner saves operating hours from settings', function () {
    $shop = driverShop();

    $this->withoutMiddleware(ownerGates())->actingAs($shop->owner)
        ->patch('/shop/settings/operating-hours', ['is_open_24_7' => false, 'opens_at' => '08:00', 'closes_at' => '17:00'])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->is_open_24_7)->toBeFalse()
        ->and($shop->fresh()->opens_at)->toStartWith('08:00');

    $this->withoutMiddleware(ownerGates())->actingAs($shop->owner)
        ->patch('/shop/settings/operating-hours', ['is_open_24_7' => false, 'opens_at' => '08:00', 'closes_at' => ''])
        ->assertSessionHasErrors('closes_at');

    $this->withoutMiddleware(ownerGates())->actingAs($shop->owner)
        ->patch('/shop/settings/operating-hours', ['is_open_24_7' => true])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->is_open_24_7)->toBeTrue();
});

function ownerGates(): array
{
    return [
        App\Http\Middleware\EnforceOwnerPlatformPermissions::class,
        App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted::class,
    ];
}
