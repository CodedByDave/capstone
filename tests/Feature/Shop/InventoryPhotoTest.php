<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Storage::fake('public');
});

function inventoryPhotoOwner(): User
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Photo Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Photo Laundry',
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

    return $owner->fresh();
}

function inventoryPhotoItem(array $overrides = []): array
{
    return array_merge([
        'name' => 'Detergent',
        'unit' => 'pcs',
        'package_unit' => 'box',
        'package_size' => 12,
        'quantity' => 10,
        'min_stock' => 2,
        'status' => 'active',
    ], $overrides);
}

it('stores an uploaded photo when an item is added', function () {
    $owner = inventoryPhotoOwner();

    $this->actingAs($owner)
        ->post('/shop/inventory', inventoryPhotoItem(['photo' => UploadedFile::fake()->image('item.jpg')]))
        ->assertSessionHasNoErrors();

    $path = Inventory::where('name', 'Detergent')->firstOrFail()->photo_path;

    expect($path)->toStartWith('inventory-photos/');
    Storage::disk('public')->assertExists($path);
});

it('adds an item without a photo', function () {
    $owner = inventoryPhotoOwner();

    $this->actingAs($owner)->post('/shop/inventory', inventoryPhotoItem())->assertSessionHasNoErrors();

    expect(Inventory::where('name', 'Detergent')->firstOrFail()->photo_path)->toBeNull();
});

it('rejects a photo that is not an image', function () {
    $owner = inventoryPhotoOwner();

    $this->actingAs($owner)
        ->post('/shop/inventory', inventoryPhotoItem(['photo' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')]))
        ->assertSessionHasErrors('photo');
});

it('replaces and removes the photo when an item is edited', function () {
    $owner = inventoryPhotoOwner();
    $this->actingAs($owner)->post('/shop/inventory', inventoryPhotoItem(['photo' => UploadedFile::fake()->image('a.jpg')]));
    $item = Inventory::where('name', 'Detergent')->firstOrFail();
    $first = $item->photo_path;

    $this->actingAs($owner)
        ->put("/shop/inventory/{$item->id}", inventoryPhotoItem(['photo' => UploadedFile::fake()->image('b.jpg')]))
        ->assertSessionHasNoErrors();

    $second = $item->fresh()->photo_path;
    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    $this->actingAs($owner)
        ->put("/shop/inventory/{$item->id}", inventoryPhotoItem(['remove_photo' => true]))
        ->assertSessionHasNoErrors();

    expect($item->fresh()->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($second);
});
