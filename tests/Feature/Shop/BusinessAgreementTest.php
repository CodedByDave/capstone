<?php

use App\Enums\AccountType;
use App\Models\BusinessAgreement;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function agreementOwner(string $status = 'pending'): array
{
    $owner = User::factory()->create([
        'name' => 'Maria Santos',
        'role' => AccountType::ShopOwner->value,
    ]);

    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Maria Clean Laundry',
        'phone' => '09171234567',
        'block_street' => '123 Aguinaldo Highway',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I',
        'postal_code' => '4103',
        'status' => $status,
    ]);

    return [$owner, $shop];
}

function agreementSignature(): array
{
    $agreement = BusinessAgreement::query()->where('is_active', true)->firstOrFail();

    return [
        'agreement_public_id' => $agreement->public_id,
        'signer_name' => 'Maria Santos',
        'signer_role' => 'Owner',
        'signature_method' => 'uploaded',
        'signature_image' => UploadedFile::fake()->createWithContent(
            'owner-signature.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        ),
        'signer_authority_confirmed' => true,
        'agreement_accepted' => true,
    ];
}

function trialPayload(array $overrides = []): array
{
    return array_merge([
        'shop_name' => 'Maria Clean Laundry',
        'phone' => '09171234567',
        'block_street' => '123 Aguinaldo Highway',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I',
        'postal_code' => '4103',
    ], $overrides);
}

function platformSignature(): array
{
    return [
        'platform_signer_name' => 'LaundryHub Administrator',
        'platform_signer_role' => 'Authorized Platform Representative',
        'platform_signature_method' => 'uploaded',
        'platform_signature_image' => UploadedFile::fake()->createWithContent(
            'platform-signature.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        ),
        'platform_signer_authority_confirmed' => true,
    ];
}

function paidApplicationPayload(array $overrides = []): array
{
    return array_merge([
        'plan_name' => 'Standard',
        'billing_months' => 1,
        'shop_name' => 'Maria Clean Laundry',
        'email' => 'ignored@example.com',
        'phone' => '09171234567',
        'block_street' => '123 Aguinaldo Highway',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I',
        'postal_code' => '4103',
        'kyc_bir' => UploadedFile::fake()->create('bir.pdf', 50, 'application/pdf'),
        'kyc_dti' => UploadedFile::fake()->create('dti.pdf', 50, 'application/pdf'),
        'kyc_mayors' => UploadedFile::fake()->create('mayors.pdf', 50, 'application/pdf'),
        'bir_expiry_date' => now()->addYear()->toDateString(),
        'dti_expiry_date' => now()->addYear()->toDateString(),
        'mayors_expiry_date' => now()->addYear()->toDateString(),
    ], $overrides);
}

test('a free trial cannot activate without agreement acceptance', function () {
    [$owner, $shop] = agreementOwner();

    $this->actingAs($owner)
        ->post(route('trial.start'), trialPayload())
        ->assertSessionHasErrors([
            'agreement_public_id',
            'signer_name',
            'signer_role',
            'signer_authority_confirmed',
            'agreement_accepted',
        ]);

    $this->assertDatabaseMissing('orders', ['user_id' => $owner->id, 'is_trial' => true]);
    expect($shop->fresh()->status)->toBe('pending');
});

test('starting a trial records the signed agreement atomically', function () {
    Storage::fake('private');
    [$owner, $shop] = agreementOwner();

    $this->actingAs($owner)
        ->post(route('trial.start'), trialPayload(agreementSignature()))
        ->assertRedirect(route('shop.dashboard'));

    $order = $owner->orders()->where('is_trial', true)->firstOrFail();

    $this->assertDatabaseHas('business_agreement_acceptances', [
        'shop_id' => $shop->id,
        'user_id' => $owner->id,
        'order_id' => $order->id,
        'signer_name' => 'Maria Santos',
        'signer_role' => 'Owner',
        'signature_method' => 'uploaded',
    ]);
    expect($shop->fresh()->agreementAcceptances()->firstOrFail()->signature_path)->not->toBeNull();
    expect($shop->fresh()->status)->toBe('active');
});

test('a paid application cannot bypass agreement acceptance', function () {
    Storage::fake('private');
    [$owner] = agreementOwner();

    $this->actingAs($owner)
        ->post(route('checkout.process'), paidApplicationPayload())
        ->assertSessionHasErrors(['agreement_public_id', 'agreement_accepted']);

    $this->assertDatabaseMissing('orders', ['user_id' => $owner->id]);
});

test('a paid application stores the agreement version signature and audit metadata', function () {
    Storage::fake('private');
    [$owner, $shop] = agreementOwner();

    $this->actingAs($owner)
        ->withHeader('User-Agent', 'Agreement Feature Test')
        ->post(route('checkout.process'), paidApplicationPayload(agreementSignature()))
        ->assertRedirect(route('shop.dashboard'));

    $order = $owner->orders()->where('status', 'pending')->firstOrFail();
    $agreement = BusinessAgreement::query()->where('is_active', true)->firstOrFail();

    $this->assertDatabaseHas('business_agreement_acceptances', [
        'business_agreement_id' => $agreement->id,
        'shop_id' => $shop->id,
        'order_id' => $order->id,
        'content_hash' => $agreement->content_hash,
        'user_agent' => 'Agreement Feature Test',
    ]);
});

test('an active legacy business is redirected until the current agreement is accepted', function () {
    Storage::fake('private');
    [$owner] = agreementOwner('active');

    $this->actingAs($owner)
        ->get(route('shop.settings'))
        ->assertRedirect(route('shop.agreement.show'));

    $this->actingAs($owner)
        ->post(route('shop.agreement.accept'), agreementSignature())
        ->assertRedirect(route('shop.dashboard'));

    $this->actingAs($owner)
        ->get(route('shop.agreement.show'))
        ->assertOk();

    $this->actingAs($owner)
        ->get(route('shop.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/Dashboard')
            ->where('agreement.accepted', true));
});

test('a new agreement acceptance requires an owner signature image', function () {
    Storage::fake('private');
    [$owner] = agreementOwner();
    $payload = agreementSignature();
    unset($payload['signature_image']);

    $this->actingAs($owner)
        ->post(route('trial.start'), trialPayload($payload))
        ->assertSessionHasErrors('signature_image');

    $this->assertDatabaseMissing('orders', ['user_id' => $owner->id, 'is_trial' => true]);
    $this->assertDatabaseCount('business_agreement_acceptances', 0);
});

test('an owner and administrator can view a stored signature but unrelated users cannot', function () {
    Storage::fake('private');
    [$owner] = agreementOwner();

    $this->actingAs($owner)
        ->post(route('shop.agreement.accept'), agreementSignature())
        ->assertRedirect(route('shop.dashboard'));

    $acceptance = $owner->shop->agreementAcceptances()->firstOrFail();
    $signatureRoute = route('business-agreement.signature', $acceptance->public_id);

    $this->actingAs($owner)->get($signatureRoute)->assertOk()->assertHeader('Content-Type', 'image/png');

    $administrator = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $this->actingAs($administrator)->get($signatureRoute)->assertOk();

    $unrelatedOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $this->actingAs($unrelatedOwner)->get($signatureRoute)->assertForbidden();
});

test('a non-owner cannot sign a business agreement', function () {
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)
        ->post(route('shop.agreement.accept'), agreementSignature())
        ->assertRedirect(route(AccountType::Customer->dashboardRoute()));

    $this->assertDatabaseCount('business_agreement_acceptances', 0);
});

test('admin approval countersigns the agreement and notifies the shop owner', function () {
    Storage::fake('private');
    [$owner] = agreementOwner();
    $admin = User::factory()->create([
        'name' => 'LaundryHub Administrator',
        'role' => AccountType::SuperAdmin->value,
    ]);

    $this->actingAs($owner)
        ->post(route('checkout.process'), paidApplicationPayload(agreementSignature()))
        ->assertRedirect(route('shop.dashboard'));

    $order = $owner->orders()->where('status', 'pending')->firstOrFail();

    $this->actingAs($admin)
        ->withHeader('User-Agent', 'Platform Signature Test')
        ->post(route('admin.orders.approve', $order->public_id), platformSignature())
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('approved');
    $this->assertDatabaseHas('business_agreement_platform_signatures', [
        'business_agreement_acceptance_id' => $order->agreementAcceptance->id,
        'admin_user_id' => $admin->id,
        'signer_name' => 'LaundryHub Administrator',
        'signature_method' => 'uploaded',
        'user_agent' => 'Platform Signature Test',
    ]);
    expect($owner->fresh()->notifications()->count())->toBe(1);

    $platformSignature = $order->agreementAcceptance->platformSignature()->firstOrFail();
    $this->actingAs($owner)
        ->get(route('business-agreement.platform-signature', $platformSignature->public_id))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    $this->actingAs($owner)
        ->get(route('shop.agreement.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentAgreement.acceptance.execution_status', 'fully_executed')
            ->where('currentAgreement.acceptance.platform_signature.signer_name', 'LaundryHub Administrator'));

    $this->actingAs($owner)
        ->get(route('shop.notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/Notifications')
            ->where('notifications.0.data.type', 'business_agreement_executed'));
});

test('approval cannot proceed without the platform signature', function () {
    Storage::fake('private');
    [$owner] = agreementOwner();
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);

    $this->actingAs($owner)
        ->post(route('checkout.process'), paidApplicationPayload(agreementSignature()))
        ->assertRedirect(route('shop.dashboard'));

    $order = $owner->orders()->where('status', 'pending')->firstOrFail();
    $payload = platformSignature();
    unset($payload['platform_signature_image']);

    $this->actingAs($admin)
        ->post(route('admin.orders.approve', $order->public_id), $payload)
        ->assertSessionHasErrors('platform_signature_image');

    expect($order->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('business_agreement_platform_signatures', 0);
});
