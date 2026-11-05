<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a verified customer account linked to an existing shop, for manual testing of the
 * customer side (ordering, delivery requests, live tracking). Safe to run repeatedly.
 */
class ShopCustomerSeeder extends Seeder
{
    public const OWNER_EMAIL = 'davemagno2314@gmail.com';

    // Gmail delivers "+customer" addresses to the owner's inbox, while staying a distinct login.
    public const CUSTOMER_EMAIL = 'davemagno2314+customer@gmail.com';

    public const PASSWORD = 'password123';

    public function run(): void
    {
        $owner = User::where('email', self::OWNER_EMAIL)->first();
        $shop = $owner ? Shop::where('owner_id', $owner->id)->first() : null;

        if ($shop === null) {
            $this->command?->error('No shop found for '.self::OWNER_EMAIL.'.');

            return;
        }

        User::updateOrCreate(
            ['email' => self::CUSTOMER_EMAIL],
            [
                'name' => 'Dave Customer',
                'password' => Hash::make(self::PASSWORD),
                'role' => AccountType::Customer->value,
                'referred_shop_id' => $shop->id,
                'email_verified_at' => now(),
                'otp_code' => null,
                'otp_expires_at' => null,
            ],
        );

        $this->command?->info('Customer for '.$shop->shop_name.': '.self::CUSTOMER_EMAIL.' / '.self::PASSWORD);
    }
}
