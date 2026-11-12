<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * One login per role for the pre-oral defense, all on the Fresh Fold demo shop:
 *
 *   php artisan db:seed --class=PreOralDefenseSeeder
 *
 * It builds the demo shop and its two months of activity first (DefenseDemoSeeder, skipped when it
 * already exists), then adds the super admin and supplier logins it does not make. Every run puts
 * each demo login back to its known password, verified and without two-factor, so an account a
 * panelist changed during rehearsal works again. For local or staging use only.
 */
class PreOralDefenseSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'defense.admin@laundryhub.test';

    public const ADMIN_PASSWORD = 'AdminPassword_123';

    public const SUPPLIER_EMAIL = 'defense.supplier@laundryhub.test';

    public const SUPPLIER_PASSWORD = 'SupplierPassword_123';

    /** The demo shop's supplier that gets a portal login. */
    private const SUPPLIER_NAME = 'CleanChem Trading';

    private const SUPPLIER_ITEMS = [
        ['Liquid Detergent', 'gallon', 40, 450],
        ['Fabric Conditioner', 'gallon', 32, 380],
        ['Powder Detergent', '25kg sack', 12, 1800],
        ['Color-safe Bleach', 'gallon', 0, 320],
        ['Stain Remover', '500ml bottle', 25, 165],
    ];

    public function run(): void
    {
        $this->call(DefenseDemoSeeder::class);

        $shop = Shop::query()
            ->whereHas('owner', fn ($query) => $query->where('email', DefenseDemoSeeder::OWNER_EMAIL))
            ->firstOrFail();

        DB::transaction(function () use ($shop) {
            $this->login(self::ADMIN_EMAIL, self::ADMIN_PASSWORD, [
                'name' => 'Defense Super Admin',
                'role' => AccountType::SuperAdmin->value,
            ]);

            $this->seedSupplierLogin($shop);

            // The accounts DefenseDemoSeeder made, back to their known passwords.
            foreach ([
                DefenseDemoSeeder::OWNER_EMAIL => DefenseDemoSeeder::OWNER_PASSWORD,
                DefenseDemoSeeder::CASHIER_EMAIL => DefenseDemoSeeder::CASHIER_PASSWORD,
                DefenseDemoSeeder::ATTENDANT_EMAIL => DefenseDemoSeeder::ATTENDANT_PASSWORD,
                DefenseDemoSeeder::RIDER_EMAIL => DefenseDemoSeeder::RIDER_PASSWORD,
                DefenseDemoSeeder::CUSTOMER_EMAIL => DefenseDemoSeeder::CUSTOMER_PASSWORD,
            ] as $email => $password) {
                $this->login($email, $password);
            }
        });

        $this->printAccounts($shop);
    }

    private function seedSupplierLogin(Shop $shop): void
    {
        $user = $this->login(self::SUPPLIER_EMAIL, self::SUPPLIER_PASSWORD, [
            'name' => 'Rhea Dizon',
            'role' => AccountType::Supplier->value,
            'shop_id' => $shop->id,
        ]);

        $supplier = Supplier::withTrashed()
            ->where('shop_id', $shop->id)
            ->where('name', self::SUPPLIER_NAME)
            ->first() ?? new Supplier(['shop_id' => $shop->id, 'name' => self::SUPPLIER_NAME, 'lead_time_days' => 2]);

        $supplier->fill([
            'user_id' => $user->id,
            'contact_person' => 'Rhea Dizon',
            'email' => self::SUPPLIER_EMAIL,
            'phone' => '09171230001',
            'address' => '14 Nueno Ave., Imus, Cavite',
            'status' => 'active',
        ]);
        $supplier->deleted_at = null;
        $supplier->save();

        foreach (self::SUPPLIER_ITEMS as [$name, $unit, $stock, $price]) {
            SupplierItem::firstOrCreate(
                ['supplier_id' => $supplier->id, 'name' => $name],
                ['unit' => $unit, 'stock_quantity' => $stock, 'unit_price' => $price],
            );
        }
    }

    /** Creates the login, or brings an existing one back to a known, usable state. */
    private function login(string $email, string $password, array $attributes = []): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        if (! $user->exists && $attributes === []) {
            throw new \RuntimeException("{$email} is missing; DefenseDemoSeeder should have created it.");
        }

        $user->forceFill([
            ...$attributes,
            'password' => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'deleted_at' => null,
        ])->save();

        return $user;
    }

    private function printAccounts(Shop $shop): void
    {
        $this->command?->info("Pre-oral defense accounts (shop: {$shop->shop_name})");
        $this->command?->table(['Role', 'Email', 'Password', 'Opens at'], [
            ['Super admin', self::ADMIN_EMAIL, self::ADMIN_PASSWORD, '/admin/dashboard'],
            ['Shop owner', DefenseDemoSeeder::OWNER_EMAIL, DefenseDemoSeeder::OWNER_PASSWORD, '/shop/dashboard'],
            ['Cashier (staff)', DefenseDemoSeeder::CASHIER_EMAIL, DefenseDemoSeeder::CASHIER_PASSWORD, '/staff/dashboard'],
            ['Attendant / washer (staff)', DefenseDemoSeeder::ATTENDANT_EMAIL, DefenseDemoSeeder::ATTENDANT_PASSWORD, '/staff/dashboard'],
            ['Rider (driver app)', DefenseDemoSeeder::RIDER_EMAIL, DefenseDemoSeeder::RIDER_PASSWORD, '/driver'],
            ['Customer', DefenseDemoSeeder::CUSTOMER_EMAIL, DefenseDemoSeeder::CUSTOMER_PASSWORD, '/user/dashboard'],
            ['Supplier', self::SUPPLIER_EMAIL, self::SUPPLIER_PASSWORD, '/supplier-portal'],
        ]);
    }
}
