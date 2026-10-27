<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopServiceCsvService;
use Illuminate\Http\UploadedFile;

function csvShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Csv Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function csvFile(string $body): UploadedFile
{
    $header = implode(',', ShopServiceCsvService::COLUMNS);

    return UploadedFile::fake()->createWithContent('services.csv', $header."\n".$body);
}

it('imports valid rows for the shop', function () {
    $shop = csvShop();

    $result = app(ShopServiceCsvService::class)->import($shop->id, csvFile(
        "Wash & Fold,full_service,Wash and fold,per_kg,60,,,24,Active\n".
        "Comforter,specialty_bulk,,per_bundle,,350,5,48,Inactive\n"
    ));

    expect($result)->toBe(['imported' => 2, 'errors' => []]);

    $bundle = ShopService::where('shop_id', $shop->id)->where('service_name', 'Comforter')->first();
    expect($bundle->is_active)->toBeFalse()
        ->and((float) $bundle->bundle_price)->toBe(350.0)
        ->and($bundle->price_per_kg)->toBeNull();
});

it('imports nothing when any row is invalid or duplicated', function () {
    $shop = csvShop();
    ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Existing', 'category' => 'full_service',
        'pricing_model' => 'per_kg', 'price_per_kg' => 50, 'is_active' => true,
    ]);

    $result = app(ShopServiceCsvService::class)->import($shop->id, csvFile(
        "Good,full_service,,per_kg,60,,,,Active\n".
        "Bad,nonsense,,per_kg,60,,,,Active\n".
        "existing,full_service,,per_kg,60,,,,Active\n"
    ));

    expect($result['imported'])->toBe(0)
        ->and($result['errors'])->toHaveCount(2)
        ->and($result['errors'][0])->toStartWith('Row 3:')
        ->and($result['errors'][1])->toContain('already exists')
        ->and(ShopService::where('shop_id', $shop->id)->count())->toBe(1);
});

it('exports only the shops own services', function () {
    $shop = csvShop();
    $other = csvShop();
    foreach ([[$shop, 'Mine'], [$other, 'Theirs']] as [$s, $name]) {
        ShopService::create([
            'shop_id' => $s->id, 'service_name' => $name, 'category' => 'full_service',
            'pricing_model' => 'per_kg', 'price_per_kg' => 50, 'is_active' => true,
        ]);
    }

    $rows = app(ShopServiceCsvService::class)->exportRows($shop->id);

    expect($rows)->toHaveCount(2)
        ->and($rows[0])->toBe(ShopServiceCsvService::COLUMNS)
        ->and($rows[1][0])->toBe('Mine');
});
