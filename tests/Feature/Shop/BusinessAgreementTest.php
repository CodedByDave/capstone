<?php

use App\Enums\AccountType;
use App\Models\BusinessAgreement;
use App\Models\KycVerification;
use App\Models\Shop;
use App\Models\User;
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
        'signer_authority_confirmed' => true,
        'agreement_accepted' => true,
    ];
}

/** The free trial starts only after an admin approves the business documents. */
function approvedKyc(User $owner, Shop $shop): void
{
    KycVerification::create([
        'user_id' => $owner->id,
        'status' => KycVerification::STATUS_APPROVED,
        'shop_name' => $shop->shop_name,
        'phone' => $shop->phone,
        'municipality' => $shop->municipality,
        'barangay' => $shop->barangay,
        'postal_code' => $shop->postal_code,
        'kyc_bir' => 'kyc/bir.pdf',
        'kyc_dti' => 'kyc/dti.pdf',
        'kyc_mayors' => 'kyc/mayors.pdf',
        'reviewed_at' => now(),
    ]);
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

test('starting a trial records the accepted agreement atomically', function () {
    Storage::fake('private');
    [$owner, $shop] = agreementOwner();
    approvedKyc($owner, $shop);

    $this->actingAs($owner)
        ->post(route('trial.start'), trialPayload(agreementSignature()))
        ->assertRedirect(route('shop.setup.show'));

    $order = $owner->orders()->where('is_trial', true)->firstOrFail();

    $this->assertDatabaseHas('business_agreement_acceptances', [
        'shop_id' => $shop->id,
        'user_id' => $owner->id,
        'order_id' => $order->id,
        'signer_name' => 'Maria Santos',
        'signer_role' => 'Owner',
    ]);
    // Acceptance is by name: no signature image is stored.
    expect($shop->fresh()->agreementAcceptances()->firstOrFail()->signature_path)->toBeNull();
    expect($shop->fresh()->status)->toBe('pending_setup');
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

test('an agreement is accepted with a name and no signature image', function () {
    Storage::fake('private');
    [$owner, $shop] = agreementOwner('active');

    $this->actingAs($owner)
        ->post(route('shop.agreement.accept'), agreementSignature())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('shop.dashboard'));

    $acceptance = $shop->agreementAcceptances()->firstOrFail();
    expect($acceptance->signer_name)->toBe('Maria Santos')
        ->and($acceptance->signature_path)->toBeNull()
        ->and($acceptance->signature_method)->toBeNull();
    Storage::disk('private')->assertDirectoryEmpty('/');
});

test('the shop sees an accepted agreement without any signature status', function () {
    Storage::fake('private');
    [$owner] = agreementOwner('active');

    $this->actingAs($owner)->post(route('shop.agreement.accept'), agreementSignature());

    $this->actingAs($owner)
        ->get(route('shop.agreement.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentAgreement.accepted', true)
            ->where('currentAgreement.acceptance.signer_name', 'Maria Santos')
            ->missing('currentAgreement.acceptance.signature_url')
            ->missing('currentAgreement.acceptance.execution_status')
            ->missing('currentAgreement.acceptance.platform_signature'));
});

test('a non-owner cannot accept a business agreement', function () {
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)
        ->post(route('shop.agreement.accept'), agreementSignature())
        ->assertRedirect(route(AccountType::Customer->dashboardRoute()));

    $this->assertDatabaseCount('business_agreement_acceptances', 0);
});

test('the agreement preview renders a PDF', function () {
    [$owner] = agreementOwner();

    $response = $this->actingAs($owner)->post(route('business-agreement.preview'), [
        'business_name' => 'Maria Clean Laundry',
        'signer_name' => 'Maria Santos',
        'signer_role' => 'Owner',
    ]);

    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($response->getContent(), 0, 5))->toBe('%PDF-');
    $this->assertDatabaseCount('business_agreement_acceptances', 0);
});

test('the agreement preview is only for owners', function () {
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $this->actingAs($customer)->post(route('business-agreement.preview'))->assertForbidden();
});

test('a stored agreement PDF is visible to the owner and admin but not to unrelated users', function () {
    Storage::fake('private');
    [$owner] = agreementOwner();

    $this->actingAs($owner)
        ->post(route('shop.agreement.accept'), agreementSignature())
        ->assertRedirect(route('shop.dashboard'));

    $acceptance = $owner->shop->agreementAcceptances()->firstOrFail();
    $pdfRoute = route('business-agreement.pdf', $acceptance->public_id);

    $response = $this->actingAs($owner)->get($pdfRoute)->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($response->getContent(), 0, 5))->toBe('%PDF-');

    $administrator = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $this->actingAs($administrator)->get($pdfRoute)->assertOk();

    $unrelatedOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $this->actingAs($unrelatedOwner)->get($pdfRoute)->assertForbidden();
});
