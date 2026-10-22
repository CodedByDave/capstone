<?php

use App\Enums\AccountType;
use App\Exceptions\PlatformRatingException;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\Order;
use App\Models\PlatformRating;
use App\Models\PlatformRolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\PlatformRatingService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A set-up shop with a paid plan. */
function platformShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Platform Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ], $overrides));
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $shop->shop_name,
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 3800,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

/** The shop finished an order this many days ago. */
function shopCompletedOrder(Shop $shop, int $daysAgo = 2, ?string $completedAt = 'set'): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'SHP-'.strtoupper(uniqid()),
        'customer_name' => 'Walk In',
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'paid',
        'status' => 'completed',
        'completed_at' => $completedAt === 'set' ? now()->subDays($daysAgo) : null,
    ]);
}

function promptedShopPage(User $user)
{
    return test()->actingAs($user)->get('/shop/settings');
}

// ── When the owner is asked ──────────────────────────────────────────────────

test('an owner who just completed an order is asked to rate the platform', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 2);

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', true));
});

test('no prompt without a recent completed order', function () {
    $quiet = platformShop();
    $stale = platformShop();
    shopCompletedOrder($stale, 20);

    promptedShopPage($quiet->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));
    promptedShopPage($stale->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));
});

test('completing an order in the edit form stamps its completion time and starts the prompt', function () {
    $shop = platformShop();
    $order = shopCompletedOrder($shop, 0, null);
    $order->update(['status' => 'in_progress', 'payment_status' => 'unpaid']);

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));

    $this->actingAs($shop->owner)
        ->put("/shop/operations/orders/{$order->id}", [
            'customer_name' => 'Walk In',
            'customer_phone' => '09181234567',
            'service_id' => $order->service_id,
            'pickup_type' => 'walk_in',
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
            'status' => 'completed',
        ])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->completed_at)->not->toBeNull();

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', true));
});

test('an owner whose platform role cannot use the rating endpoints is never prompted', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 1);

    // The rating endpoints sit behind the shop.dashboard platform permission; take it from owners.
    PlatformRolePermission::query()
        ->where('permission', 'shop.dashboard')
        ->whereHas('role', fn ($query) => $query->where('slug', 'owner'))
        ->delete();

    expect(app(PlatformRatingService::class)->shouldPrompt($shop->owner))->toBeFalse();

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));
});

test('a second submit for the same owner cannot add another rating', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 1);
    $service = app(PlatformRatingService::class);

    $service->submit($shop->owner, 5, null);

    expect(fn () => $service->submit($shop->owner, 1, 'again'))->toThrow(PlatformRatingException::class)
        ->and(PlatformRating::count())->toBe(1)
        ->and(PlatformRating::first()->rating)->toBe(5);
});

test('maybe later is ignored when no prompt was due', function () {
    $shop = platformShop(['platform_rating_prompted_at' => now()->subDays(10)]);
    $before = $shop->platform_rating_prompted_at->toDateTimeString();
    shopCompletedOrder($shop, 1);

    $this->actingAs($shop->owner)->post('/shop/platform-rating/dismiss')->assertRedirect();

    expect($shop->fresh()->platform_rating_prompted_at->toDateTimeString())->toBe($before);
});

test('the owner is asked at most once a month', function () {
    $shop = platformShop(['platform_rating_prompted_at' => now()->subDays(10)]);
    shopCompletedOrder($shop, 1);

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));

    $shop->update(['platform_rating_prompted_at' => now()->subDays(31)]);

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', true));
});

test('shops that have not finished setup, staff and customers are never asked', function () {
    $unfinished = platformShop(['setup_completed_at' => null]);
    shopCompletedOrder($unfinished, 1);

    $shop = platformShop();
    shopCompletedOrder($shop, 1);
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);
    Employee::create([
        'shop_id' => $shop->id, 'user_id' => $staff->id, 'employee_id' => 'PL-1', 'first_name' => 'S',
        'last_name' => 'M', 'position' => 'Staff', 'hire_date' => today(), 'status' => 'Active',
    ]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)->get('/user/dashboard')
        ->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));
    $this->actingAs($staff)->get('/staff/dashboard')
        ->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));

    expect(app(App\Services\PlatformRatingService::class)->shouldPrompt($unfinished->owner))->toBeFalse();
});

// ── Sending the rating ───────────────────────────────────────────────────────

test('the owner rates the platform and is not asked again', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 2);

    $this->actingAs($shop->owner)
        ->post('/shop/platform-rating', ['rating' => 5, 'comment' => 'Easy to run my shop'])
        ->assertSessionHasNoErrors();

    expect(PlatformRating::first())
        ->shop_id->toBe($shop->id)
        ->user_id->toBe($shop->owner_id)
        ->rating->toBe(5)
        ->comment->toBe('Easy to run my shop')
        ->and($shop->fresh()->platform_rating_prompted_at)->not->toBeNull();

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));
});

test('a second rating in the same month is refused and keeps the first', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 2);

    $this->actingAs($shop->owner);
    $this->post('/shop/platform-rating', ['rating' => 5]);
    $this->post('/shop/platform-rating', ['rating' => 1])->assertSessionHas('toast.type', 'error');

    expect(PlatformRating::count())->toBe(1)->and(PlatformRating::first()->rating)->toBe(5);
});

test('a rating is refused when the owner is not due to be asked', function () {
    $shop = platformShop();

    $this->actingAs($shop->owner)
        ->post('/shop/platform-rating', ['rating' => 4])
        ->assertSessionHas('toast.type', 'error');

    expect(PlatformRating::count())->toBe(0);
});

test('the platform rating is validated', function (array $payload, string $field) {
    $shop = platformShop();
    shopCompletedOrder($shop, 2);

    $this->actingAs($shop->owner)->post('/shop/platform-rating', $payload)->assertSessionHasErrors($field);

    expect(PlatformRating::count())->toBe(0);
})->with([
    'missing stars' => [[], 'rating'],
    'zero stars' => [['rating' => 0], 'rating'],
    'six stars' => [['rating' => 6], 'rating'],
    'comment too long' => [['rating' => 5, 'comment' => str_repeat('a', 501)], 'comment'],
]);

test('maybe later stops the prompt for a month without recording a rating', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 2);

    $this->actingAs($shop->owner)->post('/shop/platform-rating/dismiss')->assertRedirect();

    expect(PlatformRating::count())->toBe(0)
        ->and($shop->fresh()->platform_rating_prompted_at)->not->toBeNull();

    promptedShopPage($shop->owner)->assertInertia(fn ($page) => $page->where('platformRatingPrompt', false));
});

test('only the owner can rate or dismiss', function () {
    $shop = platformShop();
    shopCompletedOrder($shop, 2);
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);
    Employee::create([
        'shop_id' => $shop->id, 'user_id' => $staff->id, 'employee_id' => 'PL-2', 'first_name' => 'S',
        'last_name' => 'M', 'position' => 'Staff', 'hire_date' => today(), 'status' => 'Active',
    ]);

    $this->post('/shop/platform-rating', ['rating' => 5])->assertRedirect('/login');

    foreach ([$staff, User::factory()->create(['role' => AccountType::Customer->value])] as $intruder) {
        $this->actingAs($intruder);
        expect($this->post('/shop/platform-rating', ['rating' => 5])->status())->toBeIn([302, 403]);
        expect($this->post('/shop/platform-rating/dismiss')->status())->toBeIn([302, 403]);
    }

    expect(PlatformRating::count())->toBe(0)->and($shop->fresh()->platform_rating_prompted_at)->toBeNull();
});

// ── What the super admin sees ────────────────────────────────────────────────

function platformRatingFrom(Shop $shop, int $stars, ?string $comment = null, array $at = []): PlatformRating
{
    $rating = PlatformRating::create([
        'shop_id' => $shop->id,
        'user_id' => $shop->owner_id,
        'rating' => $stars,
        'comment' => $comment,
    ]);

    if ($at !== []) {
        $rating->forceFill($at)->save();
    }

    return $rating;
}

function platformAdmin(): User
{
    return User::factory()->create(['role' => AccountType::SuperAdmin->value]);
}

test('the super admin sees every shops platform rating with totals', function () {
    $a = platformShop(['shop_name' => 'Alpha Wash']);
    $b = platformShop(['shop_name' => 'Bravo Wash']);
    platformRatingFrom($a, 5, 'Love it');
    platformRatingFrom($b, 3, 'Needs work');
    platformRatingFrom($b, 4, null, ['created_at' => now()->subMonths(2)]);

    $this->actingAs(platformAdmin())
        ->get('/admin/platform-ratings')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/platform-ratings/Index')
            ->where('stats.count', 3)
            ->where('stats.average', 4)
            ->where('stats.this_month', 2)
            ->where('stats.distribution.5', 1)
            ->where('stats.distribution.4', 1)
            ->where('stats.distribution.3', 1)
            ->has('ratings.data', 3)
            ->where('ratings.data.0.shop_name', fn ($name) => in_array($name, ['Alpha Wash', 'Bravo Wash'], true))
            ->where('ratings.data.0.owner_email', fn ($email) => is_string($email)));
});

test('searching platform ratings for zero matches the digit and does not return everything', function () {
    $a = platformShop(['shop_name' => 'Alpha Wash']);
    $b = platformShop(['shop_name' => 'Bravo Wash']);
    foreach ([$a, $b] as $shop) {
        $shop->owner->update(['name' => 'Plain Owner', 'email' => "owner{$shop->id}@example.test"]);
    }
    platformRatingFrom($a, 5, 'Rated in 2020');
    platformRatingFrom($b, 4, 'No digits here');

    $this->actingAs(platformAdmin())
        ->get('/admin/platform-ratings?search=0')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.comment', 'Rated in 2020'));
});

test('platform ratings are filtered and sorted on the server', function () {
    $a = platformShop(['shop_name' => 'Alpha Wash']);
    $b = platformShop(['shop_name' => 'Bravo Wash']);
    platformRatingFrom($a, 5, 'Love it');
    platformRatingFrom($b, 2, 'Slow support');

    $this->actingAs(platformAdmin());

    $this->get('/admin/platform-ratings?stars=2')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.shop_name', 'Bravo Wash'));

    $this->get('/admin/platform-ratings?search=Alpha')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.rating', 5));

    $this->get('/admin/platform-ratings?search=support')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.shop_name', 'Bravo Wash'));

    $this->get('/admin/platform-ratings?sort_by=shop&sort_direction=desc')
        ->assertInertia(fn ($page) => $page->where('ratings.data.0.shop_name', 'Bravo Wash'));

    $this->getJson('/admin/platform-ratings?stars=7&sort_by=owner_password')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['stars', 'sort_by']);
});

test('only the super admin can open the platform ratings page', function () {
    $shop = platformShop();
    platformRatingFrom($shop, 4, 'private to admins');

    $this->get('/admin/platform-ratings')->assertRedirect('/login');

    foreach ([$shop->owner, User::factory()->create(['role' => AccountType::Customer->value])] as $other) {
        $this->actingAs($other);
        $response = $this->get('/admin/platform-ratings');
        expect($response->status())->toBeIn([302, 403]);
        $response->assertDontSee('private to admins');
    }
});
