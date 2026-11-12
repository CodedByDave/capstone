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
        ->post(route('shop.services.store'), photoServicePayload(['photos' => [photoFile('wash.png')]]))
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
        ->post(route('staff.services.store'), photoServicePayload(['photos' => [photoFile('wash.png')]]))
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
        ->post(route('shop.services.store'), photoServicePayload(['photos' => [$file]]))
        ->assertSessionHasErrors('photos.0');

    expect(ShopService::count())->toBe(0);
})->with([
    'a pdf' => fn () => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    'a gif' => fn () => UploadedFile::fake()->create('anim.gif', 100, 'image/gif'),
    'too large' => fn () => photoFile('big.png', 6 * 1024 * 1024),
]);

test('an owner can upload up to three photos, the first becoming the cover', function () {
    $owner = photoShopOwner();

    $this->actingAs($owner)
        ->post(route('shop.services.store'), photoServicePayload([
            'photos' => [photoFile('a.png', 1), photoFile('b.png', 2), photoFile('c.png', 3)],
        ]))
        ->assertSessionHasNoErrors();

    $service = ShopService::firstWhere('service_name', 'Wash & Fold');
    expect($service->photo_paths)->toHaveCount(3)
        ->and($service->photo_path)->toBe($service->photo_paths[0])
        ->and($service->photo_urls)->toHaveCount(3);
    foreach ($service->photo_paths as $path) {
        Storage::disk('public')->assertExists($path);
    }
});

test('a service cannot have more than three photos', function () {
    $owner = photoShopOwner();

    $this->actingAs($owner)
        ->post(route('shop.services.store'), photoServicePayload([
            'photos' => [photoFile('a.png'), photoFile('b.png'), photoFile('c.png'), photoFile('d.png')],
        ]))
        ->assertSessionHasErrors('photos');

    expect(ShopService::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

test('adding photos on edit cannot go past three in total', function () {
    $owner = photoShopOwner();
    $shop = $owner->shop;
    $saved = [
        photoFile('a.png', 1)->store("service-photos/{$shop->id}", 'public'),
        photoFile('b.png', 2)->store("service-photos/{$shop->id}", 'public'),
    ];
    $service = photoService($shop, ['photo_path' => $saved[0], 'photo_paths' => $saved]);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload([
            'photos' => [photoFile('c.png', 3), photoFile('d.png', 4)],
        ]))
        ->assertSessionHasErrors('photos');

    expect($service->fresh()->photo_paths)->toBe($saved)
        ->and(Storage::disk('public')->allFiles("service-photos/{$shop->id}"))->toHaveCount(2);
});

test('removing a saved photo deletes its file and the next one becomes the cover', function () {
    $owner = photoShopOwner();
    $shop = $owner->shop;
    $saved = [
        photoFile('a.png', 1)->store("service-photos/{$shop->id}", 'public'),
        photoFile('b.png', 2)->store("service-photos/{$shop->id}", 'public'),
    ];
    $service = photoService($shop, ['photo_path' => $saved[0], 'photo_paths' => $saved]);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload([
            'remove_photos' => [0],
            'photos' => [photoFile('c.png', 3)],
        ]))
        ->assertSessionHasNoErrors();

    $fresh = $service->fresh();
    expect($fresh->photo_paths)->toHaveCount(2)
        ->and($fresh->photo_paths[0])->toBe($saved[1])
        ->and($fresh->photo_path)->toBe($saved[1]);
    Storage::disk('public')->assertMissing($saved[0]);
    Storage::disk('public')->assertExists($fresh->photo_paths[1]);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload(['remove_photos' => [0, 1]]))
        ->assertSessionHasNoErrors();

    $fresh = $service->fresh();
    expect($fresh->photo_path)->toBeNull()->and($fresh->photo_paths)->toBeNull()
        ->and(Storage::disk('public')->allFiles("service-photos/{$shop->id}"))->toBe([]);
});

test('a service saved before multiple photos keeps its one photo', function () {
    $owner = photoShopOwner();
    $path = photoFile('old.png')->store("service-photos/{$owner->shop->id}", 'public');
    $service = photoService($owner->shop, ['photo_path' => $path]);

    expect($service->fresh()->photo_urls)->toHaveCount(1);

    $this->actingAs($owner)
        ->put(route('shop.services.update', $service), photoServicePayload(['photos' => [photoFile('new.png', 1)]]))
        ->assertSessionHasNoErrors();

    expect($service->fresh()->photo_paths)->toHaveCount(2)
        ->and($service->fresh()->photo_path)->toBe($path);
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
