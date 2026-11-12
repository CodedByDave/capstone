<?php

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\Rider;
use App\Models\Shop;
use App\Services\KycDocumentVault;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Storage::fake(KycDocumentVault::DISK);
    Mail::fake();
});

function riderEmployeePayload(array $overrides = []): array
{
    return array_merge([
        'employee_id' => '2026-'.random_int(10000, 99999),
        'first_name' => 'Rico',
        'last_name' => 'Santos',
        'email' => 'rico'.random_int(1000, 9999).'@example.com',
        'phone' => '09171234567',
        'address' => 'Poblacion, Imus, Cavite',
        'position' => 'rider',
        'employment_type' => 'full_time',
        'pay_rate' => 500,
        'pay_basis' => 'daily',
        'hire_date' => today()->toDateString(),
        'status' => 'Active',
        'vehicle_ownership' => 'own',
        'vehicle_type' => 'Motorcycle',
        'plate_number' => ' abc 1234 ',
        'license_photo' => UploadedFile::fake()->image('license.jpg', 800, 500),
    ], $overrides);
}

function riderShop(): Shop
{
    return driverShop(['rider_vehicle_types' => ['Motorcycle', 'Tricycle'], 'rider_vehicle_type' => 'Motorcycle']);
}

test('creating a rider saves the vehicle, plate number and an encrypted license photo', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload(['vehicle_type' => 'Tricycle']))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $rider = Rider::where('shop_id', $shop->id)->sole();

    expect($rider->vehicle_type)->toBe('Tricycle')
        ->and($rider->vehicle_ownership)->toBe(Rider::VEHICLE_OWN)
        ->and($rider->plate_number)->toBe('ABC 1234')
        ->and($rider->license_photo_path)->toStartWith("rider-licenses/{$shop->id}/")
        ->and($rider->license_photo_path)->toEndWith('.enc')
        ->and($rider->toArray())->not->toHaveKey('license_photo_path');

    // Stored encrypted, not as the raw image bytes.
    $stored = Storage::disk(KycDocumentVault::DISK)->get($rider->license_photo_path);
    expect(str_starts_with($stored, "\xFF\xD8"))->toBeFalse();
});

test('a rider on their own vehicle needs a vehicle from the shop list, a plate number and a license photo', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload([
            'vehicle_type' => 'Helicopter',
            'plate_number' => '',
            'license_photo' => null,
        ]))
        ->assertSessionHasErrors(['vehicle_type', 'plate_number', 'license_photo']);

    expect(Employee::where('shop_id', $shop->id)->exists())->toBeFalse();
});

test('the license must be an image', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload([
            'license_photo' => UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'),
        ]))
        ->assertSessionHasErrors(['license_photo']);
});

test('other positions do not need rider details', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload([
            'position' => 'cashier',
            'vehicle_type' => null,
            'plate_number' => null,
            'license_photo' => null,
        ]))
        ->assertSessionHasNoErrors();

    expect(Rider::where('shop_id', $shop->id)->exists())->toBeFalse();
});

test('editing a rider keeps the license on file unless a new one is uploaded', function () {
    $shop = riderShop();
    $this->actingAs($shop->owner)->post('/shop/employee', riderEmployeePayload());
    $employee = Employee::where('shop_id', $shop->id)->sole();
    $firstPath = Rider::where('employee_id', $employee->id)->value('license_photo_path');

    $update = [
        'first_name' => 'Rico', 'last_name' => 'Santos', 'position' => 'rider',
        'employment_type' => 'full_time', 'pay_rate' => 500, 'pay_basis' => 'daily',
        'hire_date' => today()->toDateString(), 'status' => 'Active',
        'vehicle_ownership' => 'own', 'vehicle_type' => 'Tricycle', 'plate_number' => 'XYZ 987',
    ];

    $this->put("/shop/employee/{$employee->public_id}", $update)->assertSessionHasNoErrors();

    $rider = Rider::where('employee_id', $employee->id)->sole();
    expect($rider->vehicle_type)->toBe('Tricycle')
        ->and($rider->plate_number)->toBe('XYZ 987')
        ->and($rider->license_photo_path)->toBe($firstPath);

    $this->post("/shop/employee/{$employee->public_id}", $update + [
        '_method' => 'put',
        'license_photo' => UploadedFile::fake()->image('new-license.png'),
    ])->assertSessionHasNoErrors();

    $newPath = $rider->fresh()->license_photo_path;
    expect($newPath)->not->toBe($firstPath);
    Storage::disk(KycDocumentVault::DISK)->assertMissing($firstPath);
    Storage::disk(KycDocumentVault::DISK)->assertExists($newPath);
});

test('the shop owner can open the license photo, another shop gets a 404', function () {
    $shop = riderShop();
    $this->actingAs($shop->owner)->post('/shop/employee', riderEmployeePayload());
    $employee = Employee::where('shop_id', $shop->id)->sole();

    $this->get("/shop/employee/{$employee->public_id}/license")
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $otherShop = riderShop();
    $this->actingAs($otherShop->owner)
        ->get("/shop/employee/{$employee->public_id}/license")
        ->assertNotFound();
});

test('the create form lists the vehicle types set for the shop', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->get('/shop/employee/create')
        ->assertInertia(fn ($page) => $page->where('vehicle_types', ['Motorcycle', 'Tricycle']));
});

test('a rider on a shop vehicle needs only a vehicle type from the shop list', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload([
            'vehicle_ownership' => 'shop',
            'vehicle_type' => 'Tricycle',
            'plate_number' => null,
            'license_photo' => null,
        ]))
        ->assertSessionHasNoErrors();

    $rider = Rider::where('shop_id', $shop->id)->sole();

    expect($rider->vehicle_ownership)->toBe(Rider::VEHICLE_SHOP)
        ->and($rider->vehicle_type)->toBe('Tricycle')
        ->and($rider->plate_number)->toBeNull()
        ->and($rider->license_photo_path)->toBeNull();
});

test('a shop vehicle must still be one of the shop vehicle types', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload([
            'vehicle_ownership' => 'shop',
            'vehicle_type' => 'Van',
            'license_photo' => null,
        ]))
        ->assertSessionHasErrors(['vehicle_type'])
        ->assertSessionDoesntHaveErrors(['plate_number', 'license_photo']);
});

test('a rider must choose whose vehicle they use', function () {
    $shop = riderShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', riderEmployeePayload(['vehicle_ownership' => null]))
        ->assertSessionHasErrors(['vehicle_ownership']);
});

test('moving a rider to a shop vehicle clears their plate number and deletes their license photo', function () {
    $shop = riderShop();
    $this->actingAs($shop->owner)->post('/shop/employee', riderEmployeePayload());
    $employee = Employee::where('shop_id', $shop->id)->sole();
    $licensePath = Rider::where('employee_id', $employee->id)->value('license_photo_path');

    $this->put("/shop/employee/{$employee->public_id}", [
        'first_name' => 'Rico', 'last_name' => 'Santos', 'position' => 'rider',
        'employment_type' => 'full_time', 'pay_rate' => 500, 'pay_basis' => 'daily',
        'hire_date' => today()->toDateString(), 'status' => 'Active',
        'vehicle_ownership' => 'shop', 'vehicle_type' => 'Motorcycle',
    ])->assertSessionHasNoErrors();

    $rider = Rider::where('employee_id', $employee->id)->sole();
    expect($rider->vehicle_ownership)->toBe(Rider::VEHICLE_SHOP)
        ->and($rider->plate_number)->toBeNull()
        ->and($rider->license_photo_path)->toBeNull();
    Storage::disk(KycDocumentVault::DISK)->assertMissing($licensePath);

    $this->get("/shop/employee/{$employee->public_id}/license")->assertNotFound();
});

test('switching a shop-vehicle rider to their own vehicle asks for the license', function () {
    $shop = riderShop();
    $this->actingAs($shop->owner)->post('/shop/employee', riderEmployeePayload([
        'vehicle_ownership' => 'shop', 'plate_number' => null, 'license_photo' => null,
    ]));
    $employee = Employee::where('shop_id', $shop->id)->sole();

    $this->put("/shop/employee/{$employee->public_id}", [
        'first_name' => 'Rico', 'last_name' => 'Santos', 'position' => 'rider',
        'employment_type' => 'full_time', 'pay_rate' => 500, 'pay_basis' => 'daily',
        'hire_date' => today()->toDateString(), 'status' => 'Active',
        'vehicle_ownership' => 'own', 'vehicle_type' => 'Motorcycle', 'plate_number' => 'ABC 1234',
    ])->assertSessionHasErrors(['license_photo']);
});

test('the edit form shows the saved vehicle choice', function () {
    $shop = riderShop();
    $this->actingAs($shop->owner)->post('/shop/employee', riderEmployeePayload());
    $employee = Employee::where('shop_id', $shop->id)->sole();

    $this->get("/shop/employee/{$employee->public_id}/edit")
        ->assertInertia(fn ($page) => $page
            ->where('rider.vehicle_ownership', 'own')
            ->where('rider.plate_number', 'ABC 1234')
            ->where('rider.has_license', true));
});
