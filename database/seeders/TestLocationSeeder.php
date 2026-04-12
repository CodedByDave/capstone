<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a test normal user and two nearby laundry shops (with services and
 * sample orders) so the "Nearby Shops" feature on the user Dashboard can be
 * tested end-to-end.
 *
 * Both shops are placed ~0.3–0.8 km from the user's coordinates.
 *
 * Test user login:
 *   email:    testuser@laundryhub.test
 *   password: password123
 *
 * When you open the Dashboard, allow location or manually visit:
 *   /user/dashboard?lat=14.5995&lng=120.9842
 */
class TestLocationSeeder extends Seeder
{
    // ── Shared coordinates (Ermita, Manila) ──────────────────────────────────
    // User is here; shops are within walking/biking distance.

    const USER_LAT = 14.5995;
    const USER_LNG = 120.9842;

    public function run(): void
    {
        // ── 1. Normal user ────────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'testuser@laundryhub.test'],
            [
                'name'     => 'Juan dela Cruz',
                'password' => Hash::make('password123'),
                'role'     => 'user',
            ]
        );

        $this->command->info('Test user: testuser@laundryhub.test / password123');

        // ── 2. Shop owners ────────────────────────────────────────────────────
        $owner1 = User::firstOrCreate(
            ['email' => 'shopowner1@laundryhub.test'],
            [
                'name'     => 'Maria Santos',
                'password' => Hash::make('password123'),
                'role'     => 'owner',
            ]
        );

        $owner2 = User::firstOrCreate(
            ['email' => 'shopowner2@laundryhub.test'],
            [
                'name'     => 'Pedro Reyes',
                'password' => Hash::make('password123'),
                'role'     => 'owner',
            ]
        );

        $owner3 = User::firstOrCreate(
            ['email' => 'shopowner3@laundryhub.test'],
            [
                'name'     => 'Rosa Lim',
                'password' => Hash::make('password123'),
                'role'     => 'owner',
            ]
        );

        // ── 3. Shops ─────────────────────────────────────────────────────────
        // Shop 1 — ~0.3 km north of user
        $shop1 = Shop::firstOrCreate(
            ['owner_id' => $owner1->id],
            [
                'shop_name'    => 'QuickWash Laundry',
                'branch_name'  => 'Ermita Branch',
                'phone'        => '09171234567',
                'block_street' => '123 Padre Faura St.',
                'municipality' => 'Manila',
                'barangay'     => 'Ermita',
                'postal_code'  => '1000',
                'latitude'     => 14.6023,
                'longitude'    => 120.9842,
                'cover_photo'  => 'https://picsum.photos/seed/quickwash/800/400',
                'status'       => 'active',
            ]
        );
        $shop1->update(['cover_photo' => 'https://picsum.photos/seed/quickwash/800/400']);

        // Shop 2 — ~0.6 km east of user
        $shop2 = Shop::firstOrCreate(
            ['owner_id' => $owner2->id],
            [
                'shop_name'    => 'FreshClean Express',
                'phone'        => '09281234567',
                'block_street' => '45 M.H. del Pilar St.',
                'municipality' => 'Manila',
                'barangay'     => 'Malate',
                'postal_code'  => '1004',
                'latitude'     => 14.5995,
                'longitude'    => 120.9897,
                'cover_photo'  => 'https://picsum.photos/seed/freshclean/800/400',
                'status'       => 'active',
            ]
        );
        $shop2->update(['cover_photo' => 'https://picsum.photos/seed/freshclean/800/400']);

        // Shop 3 — ~1.2 km south-west
        $shop3 = Shop::firstOrCreate(
            ['owner_id' => $owner3->id],
            [
                'shop_name'    => 'BrightSuds Laundromat',
                'phone'        => '09391234567',
                'block_street' => '88 Taft Ave.',
                'municipality' => 'Manila',
                'barangay'     => 'Malate',
                'postal_code'  => '1004',
                'latitude'     => 14.5887,
                'longitude'    => 120.9763,
                'cover_photo'  => 'https://picsum.photos/seed/brightsuds/800/400',
                'status'       => 'active',
            ]
        );
        $shop3->update([
            'cover_photo' => 'https://picsum.photos/seed/brightsuds/800/400',
            'gcash_qr'    => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GCash%3A09391234567%20BrightSuds%20Laundromat&margin=10',
            'maya_qr'     => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=Maya%3A09391234567%20BrightSuds%20Laundromat&margin=10',
        ]);

        $this->command->info("Shops created: {$shop1->shop_name}, {$shop2->shop_name}, {$shop3->shop_name}");

        // ── 4. Services & Pricing ─────────────────────────────────────────────
        $this->seedServices($shop1);
        $this->seedServices($shop2);
        $this->seedServices($shop3);

        // ── 5. Approved subscriptions for all shops (bypass checkout) ────────
        $this->seedApprovedOrder($owner1, $shop1);
        $this->seedApprovedOrder($owner2, $shop2);
        $this->seedApprovedOrder($owner3, $shop3);

        // ── 6. Sample orders for the test user ───────────────────────────────
        $this->seedOrders($user, $shop1, $shop2);

        // Update owner shop_id references
        $owner1->update(['shop_id' => $shop1->id]);
        $owner2->update(['shop_id' => $shop2->id]);
        $owner3->update(['shop_id' => $shop3->id]);

        $this->command->info('Done. Visit /user/dashboard?lat=' . self::USER_LAT . '&lng=' . self::USER_LNG);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function seedServices(Shop $shop): void
    {
        $services = [
            [
                'service_name'  => 'Regular Wash & Dry',
                'description'   => 'Standard washing and drying using commercial-grade machines.',
                'pricing_model' => 'per_kg',
                'price_per_kg'  => 65.00,
                'estimated_hours' => 4,
                'is_active'     => true,
            ],
            [
                'service_name'  => 'Express Wash & Dry',
                'description'   => 'Same-day service. Ready in 2–3 hours.',
                'pricing_model' => 'per_kg',
                'price_per_kg'  => 95.00,
                'estimated_hours' => 3,
                'is_active'     => true,
            ],
            [
                'service_name'  => 'Wash, Dry & Fold Bundle',
                'description'   => '7 kg bundle — ideal for weekly laundry.',
                'pricing_model' => 'per_bundle',
                'bundle_weight_kg' => 7.00,
                'bundle_price'  => 399.00,
                'estimated_hours' => 5,
                'is_active'     => true,
            ],
            [
                'service_name'  => 'Delicate / Hand-Wash',
                'description'   => 'Gentle hand-wash for delicate fabrics.',
                'pricing_model' => 'per_kg',
                'price_per_kg'  => 120.00,
                'estimated_hours' => 6,
                'is_active'     => true,
            ],
        ];

        foreach ($services as $svc) {
            ShopService::firstOrCreate(
                ['shop_id' => $shop->id, 'service_name' => $svc['service_name']],
                $svc
            );
        }
    }

    private function seedApprovedOrder(User $owner, Shop $shop): void
    {
        // Skip if this owner already has an approved/paid order
        if (Order::where('user_id', $owner->id)->whereIn('status', ['approved', 'paid'])->exists()) {
            return;
        }

        $order = Order::create([
            'user_id'           => $owner->id,
            'shop_name'         => $shop->shop_name,
            'owner_name'        => $owner->name,
            'email'             => $owner->email,
            'phone'             => $shop->phone,
            'block_street'      => $shop->block_street,
            'municipality'      => $shop->municipality,
            'barangay'          => $shop->barangay,
            'postal_code'       => $shop->postal_code,
            'plan_name'         => 'Premium',
            'billing_months'    => 12,
            'total_price'       => 80000,
            'payment_method'    => 'cash',
            'status'            => 'approved',
            'expires_at'        => now()->addYear(),
        ]);

        foreach (['HRM', 'Operations', 'Inventory Management', 'Finance Management', 'Reports & Analytics'] as $module) {
            $order->modules()->create(['name' => $module, 'price' => 0]);
        }
    }

    private function seedOrders(User $user, Shop $shop1, Shop $shop2): void
    {
        $service1 = $shop1->services()->where('service_name', 'Regular Wash & Dry')->first();
        $service2 = $shop2->services()->where('service_name', 'Express Wash & Dry')->first();

        if ($service1 && !ShopOrder::where('user_id', $user->id)->where('shop_id', $shop1->id)->exists()) {
            ShopOrder::create([
                'shop_id'              => $shop1->id,
                'user_id'              => $user->id,
                'order_source'         => 'online',
                'service_id'           => $service1->id,
                'order_number'         => 'ORD-TEST-00001',
                'customer_name'        => $user->name,
                'customer_phone'       => '09171112222',
                'customer_address'     => '123 Test St., Ermita, Manila',
                'estimated_weight_kg'  => 5.00,
                'actual_weight_kg'     => 5.20,
                'pickup_type'          => 'walk_in',
                'pricing_model'        => 'per_kg',
                'price_per_kg'         => 65.00,
                'additional_charges'   => 0.00,
                'discount_amount'      => 0.00,
                'total_amount'         => 338.00,
                'payment_method'       => 'cash',
                'payment_status'       => 'paid',
                'amount_paid'          => 338.00,
                'paid_at'              => now()->subDays(3),
                'status'               => 'completed',
                'estimated_completion_at' => now()->subDays(3)->addHours(4),
                'completed_at'         => now()->subDays(3)->addHours(4),
            ]);
        }

        if ($service2 && !ShopOrder::where('user_id', $user->id)->where('shop_id', $shop2->id)->exists()) {
            ShopOrder::create([
                'shop_id'              => $shop2->id,
                'user_id'              => $user->id,
                'order_source'         => 'online',
                'service_id'           => $service2->id,
                'order_number'         => 'ORD-TEST-00002',
                'customer_name'        => $user->name,
                'customer_phone'       => '09171112222',
                'customer_address'     => '123 Test St., Ermita, Manila',
                'estimated_weight_kg'  => 3.00,
                'actual_weight_kg'     => 3.10,
                'pickup_type'          => 'walk_in',
                'pricing_model'        => 'per_kg',
                'price_per_kg'         => 95.00,
                'additional_charges'   => 0.00,
                'discount_amount'      => 0.00,
                'total_amount'         => 294.50,
                'payment_method'       => 'gcash',
                'payment_status'       => 'unpaid',
                'amount_paid'          => 0.00,
                'status'               => 'in_progress',
                'estimated_completion_at' => now()->addHours(2),
            ]);
        }
    }
}
