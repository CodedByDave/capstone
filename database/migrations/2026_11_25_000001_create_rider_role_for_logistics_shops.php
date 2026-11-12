<?php

use App\Models\Shop;
use App\Models\ShopRole;
use App\Support\DefaultRolePermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Shops that already offer pickup or delivery get the rider role they would now
     * receive automatically when turning logistics on.
     */
    public function up(): void
    {
        Shop::query()
            ->where(fn ($q) => $q->where('offers_pickup', true)->orWhere('offers_delivery', true))
            ->select('id')
            ->each(function (Shop $shop) {
                ShopRole::firstOrCreate(
                    ['shop_id' => $shop->id, 'name' => DefaultRolePermissions::RIDER_ROLE],
                    ['is_default' => true],
                );
            });
    }

    public function down(): void
    {
        // The role may already have riders assigned to it; leave it in place.
    }
};
