<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function homeShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Home Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function homeCustomer(?Shop $shop = null): User
{
    return User::factory()->create([
        'role' => AccountType::Customer->value,
        'referred_shop_id' => $shop?->id,
    ]);
}

function giveNotification(User $user, array $overrides = []): string
{
    $id = (string) Str::uuid();

    DB::table('notifications')->insert(array_merge([
        'id' => $id,
        'type' => 'App\\Notifications\\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'placed', 'title' => 'Order placed', 'body' => 'Received.']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));

    return $id;
}

function makePromotion(Shop $shop, array $overrides = []): Promotion
{
    return Promotion::create(array_merge([
        'shop_id' => $shop->id,
        'name' => 'Senior & PWD',
        'description' => 'Valid ID required at pickup.',
        'type' => 'percentage',
        'value' => 20,
        'is_active' => true,
    ], $overrides));
}

it('shows the referred shop\'s active promotions on the customer home page', function () {
    $shop = homeShop();
    makePromotion($shop);
    makePromotion($shop, ['name' => 'Switched off', 'is_active' => false]);
    makePromotion($shop, ['name' => 'Expired', 'ends_at' => now()->subDay()->toDateString()]);
    makePromotion(homeShop(), ['name' => 'Other shop']);

    $this->actingAs(homeCustomer($shop))
        ->get('/user/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('promotions', 1)
            ->where('promotions.0.name', 'Senior & PWD')
            ->where('promotions.0.headline', '20% off'));
});

it('formats a fixed-amount promotion headline in pesos', function () {
    $shop = homeShop();
    makePromotion($shop, ['type' => 'fixed', 'value' => 50]);

    $this->actingAs(homeCustomer($shop))
        ->get('/user/dashboard')
        ->assertInertia(fn (Assert $page) => $page->where('promotions.0.headline', '₱50 off'));
});

it('returns no promotions for a customer without a shop', function () {
    makePromotion(homeShop());

    $this->actingAs(homeCustomer())
        ->get('/user/dashboard')
        ->assertInertia(fn (Assert $page) => $page->has('promotions', 0));
});

it('does not mark notifications as read just by opening the page', function () {
    $customer = homeCustomer();
    giveNotification($customer);

    $this->actingAs($customer)->get('/user/notifications')->assertOk();

    expect($customer->unreadNotifications()->count())->toBe(1);
});

it('marks a single notification as read', function () {
    $customer = homeCustomer();
    $first = giveNotification($customer);
    giveNotification($customer);

    $this->actingAs($customer)
        ->post("/user/notifications/{$first}/read")
        ->assertRedirect();

    expect($customer->unreadNotifications()->count())->toBe(1)
        ->and($customer->notifications()->whereKey($first)->first()->read_at)->not->toBeNull();
});

it('cannot mark another customer\'s notification as read', function () {
    $owner = homeCustomer();
    $other = homeCustomer();
    $id = giveNotification($owner);

    $this->actingAs($other)->post("/user/notifications/{$id}/read")->assertRedirect();

    expect($owner->unreadNotifications()->count())->toBe(1);
});

it('marks every notification as read', function () {
    $customer = homeCustomer();
    giveNotification($customer);
    giveNotification($customer);

    $this->actingAs($customer)->post('/user/notifications/read-all')->assertRedirect();

    expect($customer->unreadNotifications()->count())->toBe(0);
});

it('counts expired and not-yet-started promotions as inactive in the shop stats', function () {
    $shop = homeShop();
    makePromotion($shop);
    makePromotion($shop, ['name' => 'Expired', 'ends_at' => now()->subDay()->toDateString()]);
    makePromotion($shop, ['name' => 'Later', 'starts_at' => now()->addDay()->toDateString()]);
    makePromotion($shop, ['name' => 'Off', 'is_active' => false]);

    $stats = app(\App\Repositories\PromotionRepository::class)->stats($shop->id);

    expect($stats)->toMatchArray(['total' => 4, 'active' => 1, 'inactive' => 3]);
});
