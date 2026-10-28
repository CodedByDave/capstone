<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\OrderComplaint;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Notifications\OrderComplaintNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Storage::fake('private');
});

function complaintShop(string $name = 'Complaint Laundry'): Shop
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

function complaintCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

/** A completed online order that can have a problem reported unless the overrides say otherwise. */
function complaintOrder(Shop $shop, User $customer, array $overrides = []): ShopOrder
{
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
        'customer_name' => $customer->name,
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'paid',
        'amount_paid' => 180,
        'status' => 'completed',
        'completed_at' => now()->subDay(),
    ], $overrides));
}

function reportProblem(ShopOrder $order, array $payload = [])
{
    return test()->post("/user/orders/{$order->payment_public_id}/complaints", array_merge([
        'type' => 'stain_damage',
        'description' => 'My white t-shirt came back with a yellow stain on the sleeve.',
    ], $payload));
}

/** A real 1x1 PNG, so the tests do not depend on the GD extension. */
function complaintPhoto(string $name = 'stain.png', int $padBytes = 0): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

    return UploadedFile::fake()->createWithContent($name, $png.str_repeat(chr(0), $padBytes));
}

function complaintFor(ShopOrder $order, array $overrides = []): OrderComplaint
{
    return OrderComplaint::create(array_merge([
        'shop_order_id' => $order->id,
        'shop_id' => $order->shop_id,
        'user_id' => $order->user_id,
        'type' => 'stain_damage',
        'description' => 'There is a stain on my t-shirt.',
        'status' => 'open',
    ], $overrides));
}

function complaintStaff(Shop $shop, array $actions): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'CP-'.$user->id,
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    $role = 'clerk-'.$user->id;
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => $role]);

    foreach ($actions as $action) {
        RolePermission::firstOrCreate(['shop_id' => $shop->id, 'role' => $role, 'module' => 'Operations', 'action' => $action]);
    }

    return $user;
}

// ── Customer: filing ─────────────────────────────────────────────────────────

test('a customer reports a problem with photos and the shop owner is notified', function () {
    Notification::fake();
    $shop = complaintShop();
    $customer = complaintCustomer();
    $order = complaintOrder($shop, $customer);

    $this->actingAs($customer);
    reportProblem($order, [
        'photos' => [
            complaintPhoto('stain1.png'),
            complaintPhoto('stain2.png'),
        ],
    ])->assertRedirect()->assertSessionHas('toast.type', 'success');

    $complaint = OrderComplaint::sole();
    expect($complaint->shop_id)->toBe($shop->id)
        ->and($complaint->user_id)->toBe($customer->id)
        ->and($complaint->status)->toBe('open')
        ->and($complaint->photos)->toHaveCount(2);

    foreach ($complaint->photos as $photo) {
        Storage::disk('private')->assertExists($photo->path);
    }

    Notification::assertSentTo($shop->owner, OrderComplaintNotification::class);
});

test('a customer can report a problem without photos', function () {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer);

    $this->actingAs($customer);
    reportProblem($order)->assertSessionHas('toast.type', 'success');

    expect(OrderComplaint::sole()->photos)->toHaveCount(0);
});

test('the report is validated', function (array $payload, string $field) {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer);

    $this->actingAs($customer);
    reportProblem($order, $payload)->assertSessionHasErrors($field);

    expect(OrderComplaint::count())->toBe(0);
})->with([
    'missing type' => [['type' => ''], 'type'],
    'unknown type' => [['type' => 'bogus'], 'type'],
    'short description' => [['description' => 'stain'], 'description'],
    'long description' => [['description' => str_repeat('a', 1001)], 'description'],
]);

test('photos are limited by count, type and size', function () {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer);
    $this->actingAs($customer);

    reportProblem($order, ['photos' => array_map(fn ($n) => complaintPhoto("p{$n}.png"), range(1, 4))])
        ->assertSessionHasErrors('photos');
    reportProblem($order, ['photos' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')]])
        ->assertSessionHasErrors('photos.0');
    reportProblem($order, ['photos' => [complaintPhoto('big.png', 6 * 1024 * 1024)]])
        ->assertSessionHasErrors('photos.0');

    expect(OrderComplaint::count())->toBe(0);
});

test('a problem cannot be reported on an order that is not finished', function () {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer, ['status' => 'in_progress', 'completed_at' => null]);

    $this->actingAs($customer);
    reportProblem($order)->assertSessionHas('toast.type', 'error');

    expect(OrderComplaint::count())->toBe(0);
});

test('a problem cannot be reported after the 30 day window', function () {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer, ['completed_at' => now()->subDays(31)]);

    $this->actingAs($customer);
    reportProblem($order)->assertSessionHas('toast.type', 'error');

    expect(OrderComplaint::count())->toBe(0);
});

test('an order can have at most two active reports', function () {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer);
    complaintFor($order);
    complaintFor($order, ['status' => 'under_review']);

    $this->actingAs($customer);
    reportProblem($order)->assertSessionHas('toast.type', 'error');
    expect(OrderComplaint::count())->toBe(2);

    // A closed report no longer counts toward the cap.
    OrderComplaint::first()->update(['status' => 'resolved']);
    reportProblem($order)->assertSessionHas('toast.type', 'success');
    expect(OrderComplaint::count())->toBe(3);
});

test('a customer cannot report on someone elses order', function () {
    $shop = complaintShop();
    $order = complaintOrder($shop, complaintCustomer());

    $this->actingAs(complaintCustomer());
    reportProblem($order)->assertNotFound();

    expect(OrderComplaint::count())->toBe(0);
});

test('only customers can report and guests are sent to login', function () {
    $shop = complaintShop();
    $order = complaintOrder($shop, complaintCustomer());

    auth()->logout();
    reportProblem($order)->assertRedirect('/login');

    $this->actingAs($shop->owner);
    reportProblem($order)->assertRedirect();
});

test('the order page shows the customers reports and whether another can be filed', function () {
    $customer = complaintCustomer();
    $order = complaintOrder(complaintShop(), $customer);
    complaintFor($order, ['status' => 'resolved', 'shop_reply' => 'We will redo it for free.']);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('order.complaints.can_report', true)
            ->has('order.complaints.items', 1)
            ->where('order.complaints.items.0.shop_reply', 'We will redo it for free.')
            ->where('order.complaints.items.0.status', 'resolved'));
});

// ── Photos ───────────────────────────────────────────────────────────────────

test('complaint photos open only for the customer and the shop that own them', function () {
    $shop = complaintShop();
    $customer = complaintCustomer();
    $order = complaintOrder($shop, $customer);

    $this->actingAs($customer);
    reportProblem($order, ['photos' => [complaintPhoto()]]);
    $photo = OrderComplaint::sole()->photos->first();

    $this->get("/user/complaints/photos/{$photo->public_id}")->assertOk();

    $this->actingAs(complaintCustomer())
        ->get("/user/complaints/photos/{$photo->public_id}")->assertNotFound();

    $this->actingAs($shop->owner)
        ->get("/shop/operations/complaints/photos/{$photo->public_id}")->assertOk();

    $this->actingAs(complaintShop('Other Shop')->owner)
        ->get("/shop/operations/complaints/photos/{$photo->public_id}")->assertNotFound();
});

// ── Shop: list ───────────────────────────────────────────────────────────────

test('the owner sees only their own shops complaints with the totals', function () {
    $mine = complaintShop('Mine');
    $theirs = complaintShop('Theirs');
    $buyer = complaintCustomer();
    complaintFor(complaintOrder($mine, $buyer));
    complaintFor(complaintOrder($mine, $buyer), ['status' => 'resolved']);
    complaintFor(complaintOrder($theirs, $buyer), ['description' => 'Belongs to another shop entirely.']);

    $this->actingAs($mine->owner)
        ->get('/shop/operations/complaints')
        ->assertOk()
        ->assertDontSee('Belongs to another shop entirely.')
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/complaints/Index')
            ->where('stats.total', 2)
            ->where('stats.open', 1)
            ->where('stats.resolved', 1)
            ->has('complaints.data', 2));
});

test('the list filters, searches and sorts on the server', function () {
    $shop = complaintShop();
    $buyer = complaintCustomer();
    complaintFor(complaintOrder($shop, $buyer), ['type' => 'stain_damage', 'description' => 'Ink stain on collar']);
    complaintFor(complaintOrder($shop, $buyer), ['type' => 'missing_item', 'description' => 'One sock is gone', 'status' => 'resolved']);

    $this->actingAs($shop->owner);

    $this->get('/shop/operations/complaints?status=resolved')
        ->assertInertia(fn ($page) => $page->has('complaints.data', 1)->where('complaints.data.0.type', 'missing_item'));

    $this->get('/shop/operations/complaints?search=collar')
        ->assertInertia(fn ($page) => $page->has('complaints.data', 1)->where('complaints.data.0.type', 'stain_damage'));

    $this->get('/shop/operations/complaints?sort_by=type&sort_direction=asc')
        ->assertInertia(fn ($page) => $page->where('complaints.data.0.type', 'missing_item'));

    $this->get('/shop/operations/complaints?status=bogus')->assertSessionHasErrors('status');
});

test('staff with Operations view see the page but staff without it do not', function () {
    $shop = complaintShop();
    complaintFor(complaintOrder($shop, complaintCustomer()));

    $this->actingAs(complaintStaff($shop, ['view']))
        ->get('/staff/operations/complaints')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('complaints.data', 1));

    $this->actingAs(complaintStaff($shop, []))
        ->get('/staff/operations/complaints')
        ->assertRedirect('/staff/dashboard');
});

test('customers cannot open the shop complaints page', function () {
    $this->actingAs(complaintCustomer())->get('/shop/operations/complaints')->assertRedirect();
});

// ── Shop: respond ────────────────────────────────────────────────────────────

test('the owner replies, resolves and the customer is notified', function () {
    Notification::fake();
    $shop = complaintShop();
    $customer = complaintCustomer();
    $complaint = complaintFor(complaintOrder($shop, $customer));

    $this->actingAs($shop->owner)
        ->patch("/shop/operations/complaints/{$complaint->public_id}", [
            'status' => 'resolved',
            'reply' => 'Sorry about that, we will re-wash it for free.',
        ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success');

    $complaint->refresh();
    expect($complaint->status)->toBe('resolved')
        ->and($complaint->shop_reply)->toBe('Sorry about that, we will re-wash it for free.')
        ->and($complaint->replied_by)->toBe($shop->owner->id)
        ->and($complaint->resolved_at)->not->toBeNull();

    Notification::assertSentTo($customer, OrderComplaintNotification::class);
    $this->assertDatabaseHas('activity_logs', ['module' => 'Operations', 'subject_id' => $complaint->id, 'shop_id' => $shop->id]);
});

test('a reply is required to resolve or reject but not to start a review', function () {
    $shop = complaintShop();
    $complaint = complaintFor(complaintOrder($shop, complaintCustomer()));
    $url = "/shop/operations/complaints/{$complaint->public_id}";

    $this->actingAs($shop->owner);
    $this->patch($url, ['status' => 'resolved'])->assertSessionHasErrors('reply');
    $this->patch($url, ['status' => 'rejected', 'reply' => ''])->assertSessionHasErrors('reply');
    $this->patch($url, ['status' => 'open'])->assertSessionHasErrors('status');

    $this->patch($url, ['status' => 'under_review'])->assertSessionHasNoErrors();
    expect($complaint->refresh()->status)->toBe('under_review')
        ->and($complaint->resolved_at)->toBeNull();
});

test('a closed complaint cannot be changed again', function () {
    $shop = complaintShop();
    $complaint = complaintFor(complaintOrder($shop, complaintCustomer()), ['status' => 'rejected', 'shop_reply' => 'Not ours.']);

    $this->actingAs($shop->owner)
        ->patch("/shop/operations/complaints/{$complaint->public_id}", ['status' => 'resolved', 'reply' => 'Changed my mind.'])
        ->assertSessionHas('toast.type', 'error');

    expect($complaint->refresh()->status)->toBe('rejected')
        ->and($complaint->shop_reply)->toBe('Not ours.');
});

test('another shop cannot respond to a complaint and gets the same not found', function () {
    $shop = complaintShop();
    $complaint = complaintFor(complaintOrder($shop, complaintCustomer()));
    $payload = ['status' => 'resolved', 'reply' => 'Hijacked.'];

    $this->actingAs(complaintShop('Other')->owner)
        ->patch("/shop/operations/complaints/{$complaint->public_id}", $payload)
        ->assertNotFound();
    $this->actingAs(complaintShop('Other 2')->owner)
        ->patch('/shop/operations/complaints/01JZZZZZZZZZZZZZZZZZZZZZZZ', $payload)
        ->assertNotFound();

    expect($complaint->refresh()->status)->toBe('open');
});

test('staff need Operations manage to respond', function () {
    $shop = complaintShop();
    $complaint = complaintFor(complaintOrder($shop, complaintCustomer()));
    $url = "/staff/operations/complaints/{$complaint->public_id}";
    $payload = ['status' => 'under_review'];

    $this->actingAs(complaintStaff($shop, ['view']))->patch($url, $payload)->assertRedirect('/staff/dashboard');
    expect($complaint->refresh()->status)->toBe('open');

    $this->actingAs(complaintStaff($shop, ['manage']))->patch($url, $payload)->assertRedirect();
    expect($complaint->refresh()->status)->toBe('under_review');
});

test('a blank reply keeps the reply the shop already sent', function () {
    $shop = complaintShop();
    $complaint = complaintFor(complaintOrder($shop, complaintCustomer()), ['status' => 'under_review', 'shop_reply' => 'We are checking.']);

    $this->actingAs($shop->owner)
        ->patch("/shop/operations/complaints/{$complaint->public_id}", ['status' => 'under_review', 'reply' => ''])
        ->assertSessionHasNoErrors();

    expect($complaint->refresh()->shop_reply)->toBe('We are checking.');
});

test('complaints on trashed orders are hidden from the list, stats and respond', function () {
    $shop = complaintShop();
    $buyer = complaintCustomer();
    $kept = complaintFor(complaintOrder($shop, $buyer));
    $trashedOrder = complaintOrder($shop, $buyer);
    $trashed = complaintFor($trashedOrder);
    $trashedOrder->delete();

    $this->actingAs($shop->owner)
        ->get('/shop/operations/complaints')
        ->assertInertia(fn ($page) => $page->where('stats.total', 1)->has('complaints.data', 1)->where('complaints.data.0.public_id', $kept->public_id));

    $this->patch("/shop/operations/complaints/{$trashed->public_id}", ['status' => 'under_review'])->assertNotFound();
});

test('photo responses are not cacheable or sniffable', function () {
    $shop = complaintShop();
    $customer = complaintCustomer();
    $order = complaintOrder($shop, $customer);

    $this->actingAs($customer);
    reportProblem($order, ['photos' => [complaintPhoto()]]);
    $photo = OrderComplaint::sole()->photos->first();

    $response = $this->get("/user/complaints/photos/{$photo->public_id}")->assertOk();
    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Cache-Control'))->toContain('no-store');
});
