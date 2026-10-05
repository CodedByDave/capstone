<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OfflineTestAccountSeeder extends Seeder
{
    public const OWNER_EMAIL = 'offline.owner@laundryhub.test';

    public const USER_EMAIL = 'offline.user@laundryhub.test';

    public const PASSWORD = 'password123';

    /**
     * Seed verified accounts for manual testing without email or OTP access.
     *
     * The owner has a pending shop but no subscription/order, so only OTP is
     * bypassed and the rest of the owner onboarding can still be tested.
     */
    public function run(): void
    {
        $owner = User::updateOrCreate(
            ['email' => self::OWNER_EMAIL],
            [
                'name' => 'Offline Test Owner',
                'password' => Hash::make(self::PASSWORD),
                'role' => AccountType::ShopOwner->value,
                'email_verified_at' => now(),
                'otp_code' => null,
                'otp_expires_at' => null,
            ],
        );

        $shop = Shop::updateOrCreate(
            ['owner_id' => $owner->id],
            [
                'shop_name' => 'Offline Test Laundry',
                'branch_name' => 'Main Branch',
                'phone' => '09171234567',
                'block_street' => '123 Test Street',
                'municipality' => 'Manila',
                'barangay' => 'Ermita',
                'postal_code' => '1000',
                'status' => 'pending',
            ],
        );

        $owner->update(['shop_id' => $shop->id]);

        foreach (['owner', 'manager', 'cashier', 'washer', 'staff'] as $role) {
            ShopRole::updateOrCreate(
                ['shop_id' => $shop->id, 'name' => $role],
                ['is_default' => true],
            );
        }

        User::updateOrCreate(
            ['email' => self::USER_EMAIL],
            [
                'name' => 'Offline Test User',
                'password' => Hash::make(self::PASSWORD),
                'role' => AccountType::Customer->value,
                'email_verified_at' => now(),
                'otp_code' => null,
                'otp_expires_at' => null,
            ],
        );

        $this->command?->info('Offline owner: '.self::OWNER_EMAIL.' / '.self::PASSWORD);
        $this->command?->info('Offline user: '.self::USER_EMAIL.' / '.self::PASSWORD);
    }
}
