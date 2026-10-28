<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Mail\OtpVerificationMail;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Services\CustomerReferralService;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

function referralShop(string $name = 'Bubbles Laundry', array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ], $overrides));
}

function referralCustomer(?Shop $shop = null): User
{
    return User::factory()->create([
        'role' => AccountType::Customer->value,
        'referred_shop_id' => $shop?->id,
    ]);
}

test('scanning a shop QR stores the shop and opens customer registration', function () {
    $shop = referralShop();

    $this->get(route('customer.join', $shop->public_id))
        ->assertRedirect(route('register.user'))
        ->assertSessionHas('referral_shop_id', $shop->id);

    $this->get(route('register.user'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/RegisterUser')
            ->where('referralShop.name', 'Bubbles Laundry'));
});

test('an unknown or inactive shop QR is rejected', function () {
    $inactive = referralShop('Closed Shop', ['status' => 'suspended']);

    $this->get(route('customer.join', $inactive->public_id))
        ->assertRedirect(route('login'))
        ->assertSessionHas('toast.type', 'error');

    $this->get(route('customer.join', (string) Str::ulid()))
        ->assertRedirect(route('login'));
});

test('customers cannot register without scanning a shop QR', function () {
    Mail::fake();

    $this->post(route('register.user.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    Mail::assertNothingSent();
    $this->get(route('register.user'))
        ->assertInertia(fn (Assert $page) => $page->where('referralShop', null));
});

test('a registered customer is linked to the shop that referred them', function () {
    Mail::fake();
    $shop = referralShop();

    $this->get(route('customer.join', $shop->public_id));

    $this->post(route('register.user.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHas('pending_registration.referred_shop_id', $shop->id);

    $code = session('pending_registration.otp_code');

    $this->post(route('otp.verify'), ['otp' => (string) $code]);

    expect(User::where('email', 'test@example.com')->value('referred_shop_id'))->toBe($shop->id);
    Mail::assertSent(OtpVerificationMail::class);
});

test('a logged in customer without a shop is linked by scanning a QR', function () {
    $shop = referralShop();
    $customer = referralCustomer();

    $this->actingAs($customer)
        ->get(route('customer.join', $shop->public_id))
        ->assertRedirect(route('user.dashboard'));

    expect($customer->fresh()->referred_shop_id)->toBe($shop->id);
});

test('a customer already linked to a shop is not moved to another shop', function () {
    $own = referralShop('Own Shop');
    $other = referralShop('Other Shop');
    $customer = referralCustomer($own);

    $this->actingAs($customer)
        ->get(route('customer.join', $other->public_id))
        ->assertRedirect(route('user.dashboard'))
        ->assertSessionHas('toast.type', 'warning');

    expect($customer->fresh()->referred_shop_id)->toBe($own->id);
});

test('customers only reach the page of their own shop', function () {
    $own = referralShop('Own Shop');
    $other = referralShop('Other Shop');
    $customer = referralCustomer($own);

    $this->actingAs($customer)->get(route('user.shops.show', $own->id))->assertOk();
    $this->actingAs($customer)->get(route('user.shops.show', $other->id))->assertNotFound();
    $this->actingAs($customer)->get(route('user.shops'))
        ->assertRedirect(route('user.shops.show', $own->id));
});

test('the customer dashboard lists only the linked shop', function () {
    $own = referralShop('Own Shop');
    referralShop('Other Shop');

    $this->actingAs(referralCustomer($own))
        ->get(route('user.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('nearbyShops', 1)
            ->where('nearbyShops.0.shop_name', 'Own Shop'));
});

test('customers cannot place an order with another shop', function () {
    $own = referralShop('Own Shop');
    $other = referralShop('Other Shop');

    $this->actingAs(referralCustomer($own))
        ->postJson(route('user.orders.store'), ['shop_id' => $other->id])
        ->assertJsonValidationErrors('shop_id');
});

test('the shop owner sees a QR link for their own shop', function () {
    $this->withoutMiddleware([EnsureCurrentBusinessAgreementAccepted::class]);

    $shop = referralShop();
    Order::create([
        'user_id' => $shop->owner_id,
        'shop_name' => $shop->shop_name,
        'owner_name' => $shop->owner->name,
        'email' => $shop->owner->email,
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

    $this->actingAs($shop->owner)
        ->get(route('shop.customer-qr'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/CustomerQr')
            ->where('joinUrl', route('customer.join', $shop->public_id)));
});

test('in production the QR link uses the public app address', function () {
    $shop = referralShop();
    config(['app.url' => 'https://laundryhub.example']);
    app()->detectEnvironment(fn () => 'production');

    expect(app(CustomerReferralService::class)->joinUrlFor($shop))
        ->toBe('https://laundryhub.example/join/'.$shop->public_id);
});

test('the shop reference is dropped from the session once the account exists', function () {
    Mail::fake();
    $shop = referralShop();

    $this->get(route('customer.join', $shop->public_id));
    $this->post(route('register.user.store'), [
        'name' => 'Test User',
        'email' => 'cleanup@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);
    $this->post(route('otp.verify'), ['otp' => (string) session('pending_registration.otp_code')]);

    expect(session()->has('referral_shop_id'))->toBeFalse();
});
