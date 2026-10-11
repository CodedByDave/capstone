<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\User;
use App\Services\BranchService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('creating a branch stores its location permits privately', function () {
    Storage::fake('private');

    $owner = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
    ]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Permit Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);

    $this->actingAs($owner);

    $branch = app(BranchService::class)->create([
        'branch_code' => 'BR-PERMIT-01',
        'name' => 'Molino Branch',
        'phone' => '09181234567',
        'address' => 'Molino I, Bacoor, Cavite',
        'status' => 'Active',
        'mayors_permit_number' => 'BAC-2026-100',
        'mayors_permit' => UploadedFile::fake()->create('mayors.pdf', 100, 'application/pdf'),
        'mayors_expiry_date' => now()->addYear()->toDateString(),
        'sanitary_permit_number' => 'SAN-2026-100',
        'sanitary_permit' => UploadedFile::fake()->create('sanitary.pdf', 100, 'application/pdf'),
        'sanitary_expiry_date' => now()->addYear()->toDateString(),
    ], $shop->id);

    expect($branch->permit_status)->toBe('submitted')
        ->and($branch->mayors_permit_path)->not->toBeNull()
        ->and($branch->sanitary_permit_path)->not->toBeNull();

    Storage::disk('private')->assertExists($branch->mayors_permit_path);
    Storage::disk('private')->assertExists($branch->sanitary_permit_path);
});
