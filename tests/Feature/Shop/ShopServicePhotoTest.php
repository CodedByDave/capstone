<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function photoShopOwner(string $name = 'Photo Laundry'): User
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);

    return $owner;
}

function photoServicePayload(array $overrides = []): array
{
    return [
        'category' => 'full_service',
        'service_name' => 'Wash & Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        ...$overrides,
    ];
}

/** A real 1x1 PNG, so the tests do not need the GD extension that fake()->image() requires. */
function photoFile(string $name = 'photo.png', int $paddingBytes = 0): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGP4z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==');

    return UploadedFile::fake()->createWithContent($name, $png.str_repeat('\0', $paddingBytes));
}

function photoService(Shop $shop, array $overrides = []): ShopService
{
    return ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Existing Service',
        'category' => 'full_service',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        ...$overrides,
    ]);
}

// Route model binding must stay active so a service of another shop is really looked up.
beforeEach(function () {
    Storage::fake('public');
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

test('an owner can upload a photo when creating a service', function () {
    $owner = photoShopOwner();

    $this->actingAs($owner)
        ->post(route('shop.services.store'), photoServicePayload(['photo' => photoFile('wash.png')]))
        ->assertRedirect('/shop/operations/services')
        ->assertSessionHasNoErrors();

    $service = ShopService::firstWhere('service_name', 'Wash & Fold');
    expect($service->photo_path)->toStartWith("service-photos/{$owner->shop->id}/")
        ->and($service->photo_url)->not->toBeNull();
    Storage::disk('public')->assertExists($service->photo_path);
});

test('a staff member can upload a photo when creating a service', function () {
    $owner = photoShopOwner();
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);
    Employee::create([
        'shop_id' => $owner->shop->id,
        'user_id' => $staff->id,
        'employee_id' => 'PHO-'.$staff->id,
        'first_name' => 'Pho',
        'last_name' => 'To',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);

    $this->withoutMiddleware()->actingAs($staff)
        ->post(route('staff.services.store'), photoServicePayload(['photo' => photoFile('wash.png')]))
        ->assertRedirect('/staff/operations/services');

    $path = ShopService::firstWhere('service_name', 'Wash & Fold')->photo_path;
    expect($path)->toStartWith("service-photos/{$owner->shop->id}/");
    Storage::disk('public')->assertExists($path);
});

test('a service can still be created without a photo', function () {
    $owner = photoShopOwner();

    $this->actingAs($owner)
        ->post(route('shop.services.store'), photoServicePayload())
        ->assertSessionHasNoErrors();

    $service = ShopService::firstWhere('service_name', 'Wash & Fold');
    expect($service->photo_path)->toBeNull()->and($service->photo_url)->toBeNull();
});

test('the service photo must be an image within the size limit', function (UploadedFile $file) {
    $owner = photoShopOwner();

    $this->actingAs($owner)
        ->post(route('shop.services.store'), photoServicePayload(['photo' => $file]))
        ->assertSessionHasErrors('photo');

    expect(ShopService::count())->toBe(0);
})->with([
    'a pdf' => fn () => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    'a gif' => fn () => UploadedFile::fake()->create('anim.gif', 100, 'image/gif'),
    'too large' => fn () => photoFile('big.png', 6 * 1024 * 1024),
]);

test('replacing the photo deletes the old file and removing it clears the column', function () {
    $owner = photoShopOwner();
    $shop = $owner->shop;
    $old = photoFile('old.png', 1)->store("service-photos/{$shop->id}", 'public');
    $service = photoService($shop, ['photo_path' => $old]);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload(['photo' => photoFile('new.png')]))
        ->assertRedirect('/shop/operations/services');

    $new = $service->fresh()->photo_path;
    expect($new)->not->toBe($old);
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($new);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload(['remove_photo' => true]))
        ->assertRedirect('/shop/operations/services');

    expect($service->fresh()->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($new);
});

test('an update without a photo field keeps the current photo', function () {
    $owner = photoShopOwner();
    $path = photoFile('keep.png')->store("service-photos/{$owner->shop->id}", 'public');
    $service = photoService($owner->shop, ['photo_path' => $path]);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload(['service_name' => 'Renamed']))
        ->assertRedirect('/shop/operations/services');

    expect($service->fresh()->service_name)->toBe('Renamed');

    expect($service->fresh()->photo_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('a shop cannot view, change, archive, toggle or restore a service of another shop', function () {
    $owner = photoShopOwner('Shop A');
    $other = photoShopOwner('Shop B');
    $foreign = photoService($other->shop, ['service_name' => 'Foreign']);

    $act = fn () => $this->actingAs($owner);

    $own = photoService($owner->shop, ['service_name' => 'Own']);
    $act()->get(route('shop.services.edit', $own))->assertOk();

    $act()->get(route('shop.services.edit', $foreign))->assertNotFound();
    $act()->put(route('shop.services.update', $foreign), photoServicePayload(['service_name' => 'Hijacked']))->assertNotFound();
    $act()->patch(route('shop.services.toggle', $foreign))->assertNotFound();
    $act()->delete(route('shop.services.destroy', $foreign))->assertNotFound();
    $act()->patch(route('shop.services.restore', $foreign))->assertNotFound();

    $foreign->refresh();
    expect($foreign->service_name)->toBe('Foreign')
        ->and($foreign->is_active)->toBeTrue()
        ->and($foreign->trashed())->toBeFalse();
});
