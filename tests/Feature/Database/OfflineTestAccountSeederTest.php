<?php

use App\Enums\AccountType;
use App\Models\ShopRole;
use App\Models\User;
use Database\Seeders\OfflineTestAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('it seeds verified offline owner and customer accounts without OTP data', function () {
    $this->seed(OfflineTestAccountSeeder::class);

    $owner = User::with('shop')->where('email', OfflineTestAccountSeeder::OWNER_EMAIL)->firstOrFail();
    $customer = User::where('email', OfflineTestAccountSeeder::USER_EMAIL)->firstOrFail();

    expect($owner->role)->toBe(AccountType::ShopOwner->value)
        ->and($owner->email_verified_at)->not->toBeNull()
        ->and($owner->otp_code)->toBeNull()
        ->and($owner->otp_expires_at)->toBeNull()
        ->and(Hash::check(OfflineTestAccountSeeder::PASSWORD, $owner->password))->toBeTrue()
        ->and($owner->shop)->not->toBeNull()
        ->and($owner->shop->status)->toBe('pending')
        ->and($owner->orders()->exists())->toBeFalse()
        ->and(ShopRole::where('shop_id', $owner->shop->id)->count())->toBe(5)
        ->and($customer->role)->toBe(AccountType::Customer->value)
        ->and($customer->email_verified_at)->not->toBeNull()
        ->and($customer->otp_code)->toBeNull()
        ->and($customer->otp_expires_at)->toBeNull()
        ->and(Hash::check(OfflineTestAccountSeeder::PASSWORD, $customer->password))->toBeTrue();
});

test('it can be run repeatedly without duplicating accounts or shop roles', function () {
    $this->seed(OfflineTestAccountSeeder::class);
    $this->seed(OfflineTestAccountSeeder::class);

    $owner = User::with('shop')
        ->where('email', OfflineTestAccountSeeder::OWNER_EMAIL)
        ->firstOrFail();

    expect(User::whereIn('email', [
        OfflineTestAccountSeeder::OWNER_EMAIL,
        OfflineTestAccountSeeder::USER_EMAIL,
    ])->count())->toBe(2)
        ->and(ShopRole::where('shop_id', $owner->shop->id)->count())->toBe(5);
});
