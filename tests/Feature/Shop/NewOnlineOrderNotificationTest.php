<?php

use App\Enums\AccountType;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopOrderService;

function onlineOrderShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Notify Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
}

function staffWithOperations(Shop $shop, string $role, array $actions): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'EMP-'.$user->id,
        'first_name' => 'Staff',
        'last_name' => $role,
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => $role]);

    foreach ($actions as $action) {
        RolePermission::create(['shop_id' => $shop->id, 'role' => $role, 'module' => 'Operations', 'action' => $action]);
    }

    return $user;
}

function placeOnlineOrder(Shop $shop): void
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    app(ShopOrderService::class)->createCustomerOrder(
        User::factory()->create(['role' => AccountType::Customer->value]),
        [
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'customer_name' => 'Online Customer',
            'customer_phone' => '09171234567',
            'estimated_weight_kg' => 3,
            'pickup_type' => 'walk_in',
            'payment_method' => 'cash',
            'customer_agreement_version' => config('customer_service_agreement.version'),
        ],
        '127.0.0.1',
        'Pest',
    );
}

test('a new online order notifies the owner and staff who can view Operations', function () {
    $shop = onlineOrderShop();
    $viewer = staffWithOperations($shop, 'cashier', ['view']);
    $manager = staffWithOperations($shop, 'manager', ['view', 'create']);
    $unrelated = staffWithOperations($shop, 'stockkeeper', []);
    $otherShopOwner = onlineOrderShop()->owner;

    placeOnlineOrder($shop);

    foreach ([$shop->owner, $viewer, $manager] as $recipient) {
        $notification = $recipient->notifications()->where('data->type', 'new_online_order')->first();
        expect($notification)->not->toBeNull();
    }

    expect($unrelated->notifications()->count())->toBe(0)
        ->and($otherShopOwner->notifications()->count())->toBe(0)
        ->and($shop->owner->notifications()->first()->data['url'])->toStartWith('/shop/operations/orders/')
        ->and($viewer->notifications()->first()->data['url'])->toStartWith('/staff/operations/orders/');
});

test('staff can open their notifications page', function () {
    $shop = onlineOrderShop();
    $staff = staffWithOperations($shop, 'cashier', ['view']);

    $this->actingAs($staff)->get('/staff/notifications')->assertOk();
});

test('the popup poll returns unread new online orders for the owner and permitted staff only', function () {
    $shop = onlineOrderShop();
    $viewer = staffWithOperations($shop, 'cashier', ['view']);
    $unrelated = staffWithOperations($shop, 'stockkeeper', []);
    placeOnlineOrder($shop);

    $this->actingAs($viewer)->getJson('/staff/notifications/poll')
        ->assertOk()
        ->assertJsonCount(1, 'popups')
        ->assertJsonPath('popups.0.type', 'new_online_order')
        ->assertJsonPath('unread', 1);

    expect($shop->owner->unreadNotifications()->count())->toBe(1);
    $this->actingAs($unrelated)->getJson('/staff/notifications/poll')->assertOk()->assertJsonCount(0, 'popups');

    $shop->owner->unreadNotifications->markAsRead();
    $this->actingAs($shop->owner)->getJson('/shop/notifications/poll')->assertOk()->assertJsonCount(0, 'popups');
});
