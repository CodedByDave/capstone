<?php

use App\Enums\AccountType;
use App\Models\BusinessAgreement;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function rejectedPlanApplication(User $owner): Order
{
    return Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Retry Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I-A',
        'postal_code' => '4103',
        'plan_name' => 'Standard',
        'billing_months' => 1,
        'total_price' => 7056,
        'status' => 'rejected',
        'rejection_reason' => 'Please submit clearer permits.',
    ]);
}

function validPlanApplicationPayload(): array
{
    $agreement = BusinessAgreement::where('is_active', true)->firstOrFail();

    return [
        'plan_name' => 'Standard',
        'billing_months' => 1,
        'shop_name' => 'Retry Laundry',
        'email' => 'ignored@example.com',
        'phone' => '09171234567',
        'block_street' => 'Robinson Imus Cavite',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I-A',
        'postal_code' => '4103',
        'kyc_bir' => UploadedFile::fake()->create('bir.pdf', 100, 'application/pdf'),
        'kyc_dti' => UploadedFile::fake()->create('dti.pdf', 100, 'application/pdf'),
        'kyc_mayors' => UploadedFile::fake()->create('mayors.pdf', 100, 'application/pdf'),
        'bir_expiry_date' => now()->addYear()->toDateString(),
        'dti_expiry_date' => now()->addYear()->toDateString(),
        'mayors_expiry_date' => now()->addYear()->toDateString(),
        'agreement_public_id' => $agreement->public_id,
        'signer_name' => 'Test Shop Owner',
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

function createRetryShop(User $owner): Shop
{
    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Retry Laundry',
        'phone' => '09171234567',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I-A',
        'postal_code' => '4103',
        'status' => 'pending',
    ]);
}

test('an owner can submit the third and final resubmission', function () {
    Storage::fake('private');
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    createRetryShop($owner);

    // Initial rejected application plus two rejected resubmissions.
    foreach (range(1, 3) as $_) {
        rejectedPlanApplication($owner);
    }

    $this->actingAs($owner)
        ->post(route('checkout.process'), validPlanApplicationPayload())
        ->assertRedirect(route('shop.dashboard'));

    expect(Order::where('user_id', $owner->id)->count())->toBe(4);
    $this->assertDatabaseHas('orders', [
        'user_id' => $owner->id,
        'status' => 'pending',
    ]);
});

test('an owner cannot submit more than three resubmissions', function () {
    Storage::fake('private');
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    createRetryShop($owner);

    // Initial application plus all three allowed resubmissions were rejected.
    foreach (range(1, 4) as $_) {
        rejectedPlanApplication($owner);
    }

    $this->actingAs($owner)
        ->from(route('shop.dashboard'))
        ->post(route('checkout.process'), validPlanApplicationPayload())
        ->assertRedirect(route('shop.dashboard'))
        ->assertSessionHas('toast.message', 'You have reached the maximum of 3 order resubmissions. Please contact support for assistance.');

    expect(Order::where('user_id', $owner->id)->count())->toBe(4);
    $this->assertDatabaseMissing('orders', [
        'user_id' => $owner->id,
        'status' => 'pending',
    ]);
});
