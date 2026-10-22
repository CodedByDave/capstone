<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Repositories\OrderRatingRepository;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A set-up shop with a paid plan, so the owner pages open. */
function listedShop(string $name): Shop
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
        'total_price' => 3800,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

/** Stores a customer's rating of a shop through a real completed order. */
function listedRating(Shop $shop, int $stars, ?string $comment = null, string $customer = 'Maria Cruz', array $at = []): OrderRating
{
    $buyer = User::factory()->create(['role' => AccountType::Customer->value, 'name' => $customer]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);
    $order = ShopOrder::create([
        'shop_id' => $shop->id,
        'user_id' => $buyer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ONL-'.strtoupper(uniqid()),
        'customer_name' => $customer,
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'paid',
        'status' => 'completed',
        'completed_at' => now()->subDay(),
    ]);

    $rating = OrderRating::create([
        'shop_order_id' => $order->id,
        'shop_id' => $shop->id,
        'user_id' => $buyer->id,
        'rating' => $stars,
        'comment' => $comment,
    ]);

    if ($at !== []) {
        $rating->forceFill($at)->save();
    }

    return $rating;
}

function listedStaff(Shop $shop, bool $canViewOperations, ?string $branch = null): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'RT-'.$user->id,
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
        'branch_name' => $branch,
    ]);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => 'clerk']);

    if ($canViewOperations) {
        RolePermission::firstOrCreate(['shop_id' => $shop->id, 'role' => 'clerk', 'module' => 'Operations', 'action' => 'view']);
    }

    return $user;
}

test('the owner sees only their own shops ratings with the totals', function () {
    $mine = listedShop('Mine');
    $theirs = listedShop('Theirs');
    foreach ([5, 5, 4, 2] as $stars) {
        listedRating($mine, $stars, "mine {$stars}");
    }
    listedRating($theirs, 1, 'belongs to another shop');

    $this->actingAs($mine->owner)
        ->get('/shop/operations/ratings')
        ->assertOk()
        ->assertDontSee('belongs to another shop')
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/ratings/Index')
            ->where('summary.count', 4)
            ->where('summary.average', 4)
            ->where('summary.distribution.5', 2)
            ->where('summary.distribution.4', 1)
            ->where('summary.distribution.3', 0)
            ->where('summary.distribution.2', 1)
            ->where('summary.distribution.1', 0)
            ->has('ratings.data', 4));
});

test('ratings are filtered, searched and sorted on the server', function () {
    $shop = listedShop('Filterable');
    listedRating($shop, 5, 'spotless', 'Ana Reyes');
    listedRating($shop, 3, 'a bit slow', 'Ben Santos');
    listedRating($shop, 1, 'lost a sock', 'Cara Lim');

    $this->actingAs($shop->owner);

    $this->get('/shop/operations/ratings?stars=3')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.comment', 'a bit slow'));

    $this->get('/shop/operations/ratings?search=sock')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.customer_name', 'Cara Lim'));

    $this->get('/shop/operations/ratings?search=Ana')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.rating', 5));

    $this->get('/shop/operations/ratings?sort_by=rating&sort_direction=asc')
        ->assertInertia(fn ($page) => $page
            ->where('ratings.data.0.rating', 1)
            ->where('ratings.data.2.rating', 5));

    $this->get('/shop/operations/ratings?sort_by=customer&sort_direction=asc')
        ->assertInertia(fn ($page) => $page->where('ratings.data.0.customer_name', 'Ana Reyes'));

    $this->get('/shop/operations/ratings?per_page=5&page=1')
        ->assertInertia(fn ($page) => $page->where('ratings.per_page', 5)->where('ratings.total', 3));
});

test('the newest ratings come first by default', function () {
    $shop = listedShop('Newest First');
    listedRating($shop, 3, 'old', 'Old', ['created_at' => now()->subDays(5)]);
    listedRating($shop, 4, 'new', 'New', ['created_at' => now()->subHour()]);

    $this->actingAs($shop->owner)
        ->get('/shop/operations/ratings')
        ->assertInertia(fn ($page) => $page->where('ratings.data.0.comment', 'new'));
});

test('invalid filters are rejected', function () {
    $shop = listedShop('Strict');

    $this->actingAs($shop->owner)
        ->getJson('/shop/operations/ratings?stars=9&sort_by=password&sort_direction=sideways')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['stars', 'sort_by', 'sort_direction']);
});

test('staff with the Operations view permission see their own shops ratings only', function () {
    $mine = listedShop('Staff Shop');
    $other = listedShop('Other Shop');
    listedRating($mine, 4, 'visible to my staff');
    listedRating($other, 2, 'hidden from my staff');

    $this->actingAs(listedStaff($mine, true))
        ->get('/staff/operations/ratings')
        ->assertOk()
        ->assertSee('visible to my staff')
        ->assertDontSee('hidden from my staff')
        ->assertInertia(fn ($page) => $page->where('summary.count', 1));
});

test('staff without the permission, customers and guests are turned away', function () {
    $shop = listedShop('Closed');
    listedRating($shop, 5, 'private feedback');

    $this->actingAs(listedStaff($shop, false))
        ->get('/staff/operations/ratings')
        ->assertRedirect(route('staff.dashboard'));

    auth()->logout();
    $this->get('/shop/operations/ratings')->assertRedirect('/login');

    $this->actingAs(User::factory()->create(['role' => AccountType::Customer->value]));
    expect($this->get('/shop/operations/ratings')->status())->toBeIn([302, 403]);
});

test('branch staff see only their branch and unassigned orders, like the orders list', function () {
    $shop = listedShop('Two Branches');
    $mine = listedRating($shop, 5, 'branch a feedback', 'Ana');
    $theirs = listedRating($shop, 1, 'branch b feedback', 'Ben');
    $either = listedRating($shop, 3, 'unassigned feedback', 'Cara');
    $mine->order->update(['branch_name' => 'Branch A']);
    $theirs->order->update(['branch_name' => 'Branch B']);
    $either->order->update(['branch_name' => null]);

    $this->actingAs(listedStaff($shop, true, 'Branch A'))
        ->get('/staff/operations/ratings')
        ->assertOk()
        ->assertSee('branch a feedback')
        ->assertSee('unassigned feedback')
        ->assertDontSee('branch b feedback')
        ->assertInertia(fn ($page) => $page
            ->where('summary.count', 2)
            ->where('summary.average', 4)
            ->has('ratings.data', 2));

    // The owner and staff without a branch still see the whole shop.
    $this->actingAs($shop->owner)->get('/shop/operations/ratings')
        ->assertInertia(fn ($page) => $page->where('summary.count', 3));
    $this->actingAs(listedStaff($shop, true))->get('/staff/operations/ratings')
        ->assertInertia(fn ($page) => $page->where('summary.count', 3));
});

test('searching for zero matches the digit and does not return everything', function () {
    $shop = listedShop('Zero Search');
    $with = listedRating($shop, 5, 'first', 'Ana Reyes');
    $without = listedRating($shop, 4, 'second', 'Ben Santos');
    $with->order->update(['order_number' => 'A-100']);
    $without->order->update(['order_number' => 'B-299']);

    $this->actingAs($shop->owner)
        ->get('/shop/operations/ratings?search=0')
        ->assertInertia(fn ($page) => $page->has('ratings.data', 1)->where('ratings.data.0.order_number', 'A-100'));
});

test('the ratings payload does not expose row ids', function () {
    $shop = listedShop('No Ids');
    listedRating($shop, 5);

    $this->actingAs($shop->owner)
        ->get('/shop/operations/ratings')
        ->assertInertia(fn ($page) => $page->missing('ratings.data.0.id'));
});

test('the repository summary keeps each shop separate and handles no ratings', function () {
    $a = listedShop('A');
    $b = listedShop('B');
    $empty = listedShop('Empty');
    listedRating($a, 5);
    listedRating($a, 4);
    listedRating($b, 1);
    $repository = app(OrderRatingRepository::class);

    expect($repository->summariesForShops([$a->id, $b->id, $empty->id]))->toBe([
        $a->id => ['average' => 4.5, 'count' => 2],
        $b->id => ['average' => 1.0, 'count' => 1],
    ])
        ->and($repository->summaryForShop($empty->id))->toBe([
            'average' => null,
            'count' => 0,
            'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ])
        ->and($repository->summariesForShops([]))->toBe([]);
});
