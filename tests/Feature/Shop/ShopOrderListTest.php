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
use App\Models\ShopService;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A set-up shop with a paid plan so the owner pages open. */
function listShop(string $name = 'List Laundry'): Shop
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

function listService(Shop $shop, string $name = 'Wash and Fold'): ShopService
{
    return ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => $name,
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);
}

/** @param  array<string, mixed>  $overrides */
function listOrder(Shop $shop, array $overrides = []): ShopOrder
{
    $createdAt = $overrides['created_at'] ?? null;
    unset($overrides['created_at']);

    $order = ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => listService($shop)->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Customer '.uniqid(),
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'unpaid',
        'status' => 'pending',
    ], $overrides));

    if ($createdAt !== null) {
        $order->forceFill(['created_at' => $createdAt])->saveQuietly();
    }

    return $order;
}

function listStaff(Shop $shop, bool $canView = true): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'OL-'.$user->id,
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    $role = $canView ? 'clerk' : 'no-access';
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => $role]);

    if ($canView) {
        RolePermission::firstOrCreate(['shop_id' => $shop->id, 'role' => $role, 'module' => 'Operations', 'action' => 'view']);
    }

    return $user;
}

/** The order numbers on the page, in the order shown. */
function listed($response): array
{
    return collect($response->viewData('page')['props']['orders']['data'])->pluck('order_number')->all();
}

test('the owner sees their own orders newest first, 15 to a page', function () {
    $shop = listShop();
    $other = listShop('Other Laundry');
    listOrder($shop, ['order_number' => 'OLD', 'created_at' => now()->subDays(3)]);
    listOrder($shop, ['order_number' => 'NEW', 'created_at' => now()->subHour()]);
    listOrder($other, ['order_number' => 'NOT-MINE']);

    $response = $this->actingAs($shop->owner)->get('/shop/operations/orders')->assertOk();

    expect(listed($response))->toBe(['NEW', 'OLD']);
    $response->assertInertia(fn ($page) => $page
        ->component('shop/operations/orders/Index')
        ->where('orders.per_page', 15)
        ->where('orders.total', 2)
        ->where('filters.sort_by', 'created_at')
        ->where('filters.sort_direction', 'desc')
        ->where('showArchived', false)
        ->has('statusSummary'));
});

test('orders sort on every column in both directions', function (string $sortBy) {
    $shop = listShop();
    $alpha = listService($shop, 'Alpha Wash');
    $zeta = listService($shop, 'Zeta Press');

    // "low" sorts first ascending on every column, "high" sorts first descending.
    listOrder($shop, [
        'order_number' => 'A-LOW', 'customer_name' => 'Aaron', 'service_id' => $alpha->id,
        'status' => 'completed', 'payment_status' => 'paid', 'total_amount' => 50,
        'created_at' => now()->subDays(5),
    ]);
    listOrder($shop, [
        'order_number' => 'Z-HIGH', 'customer_name' => 'Zoe', 'service_id' => $zeta->id,
        'status' => 'pending', 'payment_status' => 'unpaid', 'total_amount' => 900,
        'created_at' => now()->subDays(1),
    ]);

    $this->actingAs($shop->owner);

    $asc = listed($this->get("/shop/operations/orders?sort_by={$sortBy}&sort_direction=asc"));
    $desc = listed($this->get("/shop/operations/orders?sort_by={$sortBy}&sort_direction=desc"));

    expect($asc)->toBe(['A-LOW', 'Z-HIGH'])
        ->and($desc)->toBe(['Z-HIGH', 'A-LOW']);
})->with(['order_number', 'customer', 'service', 'total', 'created_at']);

test('status and payment sort in workflow order, not alphabetically', function () {
    $shop = listShop();
    foreach ([
        ['S-DONE', 'completed', 'paid'],
        ['S-PEND', 'pending', 'unpaid'],
        ['S-PROG', 'in_progress', 'partial'],
    ] as [$number, $status, $payment]) {
        listOrder($shop, ['order_number' => $number, 'status' => $status, 'payment_status' => $payment]);
    }
    $this->actingAs($shop->owner);

    expect(listed($this->get('/shop/operations/orders?sort_by=status&sort_direction=asc')))
        ->toBe(['S-PEND', 'S-PROG', 'S-DONE'])
        ->and(listed($this->get('/shop/operations/orders?sort_by=status&sort_direction=desc')))
        ->toBe(['S-DONE', 'S-PROG', 'S-PEND'])
        ->and(listed($this->get('/shop/operations/orders?sort_by=payment_status&sort_direction=asc')))
        ->toBe(['S-PEND', 'S-PROG', 'S-DONE'])
        ->and(listed($this->get('/shop/operations/orders?sort_by=payment_status&sort_direction=desc')))
        ->toBe(['S-DONE', 'S-PROG', 'S-PEND']);
});

test('the page size and page number are honoured and clamped', function () {
    $shop = listShop();
    foreach (range(1, 7) as $i) {
        listOrder($shop, ['order_number' => sprintf('N-%02d', $i), 'created_at' => now()->subMinutes(10 - $i)]);
    }
    $this->actingAs($shop->owner);

    $second = $this->get('/shop/operations/orders?per_page=5&page=2');
    expect(listed($second))->toHaveCount(2);
    $second->assertInertia(fn ($page) => $page
        ->where('orders.total', 7)
        ->where('orders.per_page', 5)
        ->where('orders.current_page', 2));

    $this->get('/shop/operations/orders?per_page=1')
        ->assertInertia(fn ($page) => $page->where('orders.per_page', 5));
    $this->get('/shop/operations/orders?per_page=500')
        ->assertInertia(fn ($page) => $page->where('orders.per_page', 100));
});

test('orders are filtered by status, payment and search', function () {
    $shop = listShop();
    listOrder($shop, ['order_number' => 'P-1', 'customer_name' => 'Ana Reyes', 'customer_phone' => '09171110000', 'status' => 'pending', 'payment_status' => 'unpaid']);
    listOrder($shop, ['order_number' => 'C-2', 'customer_name' => 'Ben Santos', 'customer_phone' => '09172220000', 'status' => 'completed', 'payment_status' => 'paid']);
    listOrder($shop, ['order_number' => 'C-3', 'customer_name' => 'Cara Lim', 'customer_phone' => '09173330000', 'status' => 'completed', 'payment_status' => 'partial']);
    $this->actingAs($shop->owner);

    expect(listed($this->get('/shop/operations/orders?status=pending')))->toBe(['P-1'])
        ->and(listed($this->get('/shop/operations/orders?payment_status=paid')))->toBe(['C-2'])
        ->and(listed($this->get('/shop/operations/orders?status=completed&payment_status=partial')))->toBe(['C-3'])
        ->and(listed($this->get('/shop/operations/orders?search=Ana')))->toBe(['P-1'])
        ->and(listed($this->get('/shop/operations/orders?search=C-2')))->toBe(['C-2'])
        ->and(listed($this->get('/shop/operations/orders?search='.substr(
            ShopOrder::where('order_number', 'P-1')->first()->getRawOriginal('customer_phone'),
            -6,
        ))))->toBe(['P-1']);
});

test('search treats percent and underscore literally and searching zero works', function () {
    $shop = listShop();
    listOrder($shop, ['order_number' => 'A-100', 'customer_name' => 'Ana']);
    listOrder($shop, ['order_number' => 'B-299', 'customer_name' => 'Ben', 'customer_phone' => '09181234567']);
    $this->actingAs($shop->owner);

    expect(listed($this->get('/shop/operations/orders?search=%25')))->toBe([])
        ->and(listed($this->get('/shop/operations/orders?search=_')))->toBe([])
        ->and(listed($this->get('/shop/operations/orders?search=A-1')))->toBe(['A-100']);
});

test('the archived view lists only deleted orders and ignores the status filters', function () {
    $shop = listShop();
    listOrder($shop, ['order_number' => 'LIVE']);
    $gone = listOrder($shop, ['order_number' => 'GONE', 'status' => 'completed']);
    $gone->delete();
    $this->actingAs($shop->owner);

    $response = $this->get('/shop/operations/orders?archived=1&status=pending&sort_by=order_number');

    expect(listed($response))->toBe(['GONE']);
    $response->assertInertia(fn ($page) => $page->where('showArchived', true));
});

test('invalid list options are rejected', function () {
    $shop = listShop();

    $this->actingAs($shop->owner)
        ->getJson('/shop/operations/orders?status=bogus&payment_status=bogus&sort_by=password&sort_direction=sideways&page=0')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status', 'payment_status', 'sort_by', 'sort_direction', 'page']);
});

test('staff see their shops orders only and need the Operations view permission', function () {
    $mine = listShop('Mine');
    $theirs = listShop('Theirs');
    listOrder($mine, ['order_number' => 'MINE-1']);
    listOrder($theirs, ['order_number' => 'THEIRS-1']);

    expect(listed($this->actingAs(listStaff($mine))->get('/staff/operations/orders')))->toBe(['MINE-1']);

    $this->actingAs(listStaff($mine, false))
        ->get('/staff/operations/orders')
        ->assertRedirect(route('staff.dashboard'));
});

test('staff see the shop orders, sorted and paged on the server', function () {
    $shop = listShop();
    listOrder($shop, ['order_number' => 'A-1', 'total_amount' => 100]);
    listOrder($shop, ['order_number' => 'A-2', 'total_amount' => 300]);
    listOrder($shop, ['order_number' => 'B-1', 'total_amount' => 250]);
    listOrder($shop, ['order_number' => 'U-1', 'total_amount' => 200]);

    $response = $this->actingAs(listStaff($shop, true))
        ->get('/staff/operations/orders?sort_by=total&sort_direction=desc&per_page=5');

    expect(listed($response))->toBe(['A-2', 'B-1', 'U-1', 'A-1']);
    $response->assertInertia(fn ($page) => $page->where('orders.total', 4));
});

test('guests and customers cannot open the orders list', function () {
    $shop = listShop();

    $this->get('/shop/operations/orders')->assertRedirect('/login');

    $this->actingAs(User::factory()->create(['role' => AccountType::Customer->value]));
    expect($this->get('/shop/operations/orders')->status())->toBeIn([302, 403]);
});
