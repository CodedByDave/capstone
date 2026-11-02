<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An item can be charged to customers as an add-on (e.g. 30 ml for ₱15) and/or sold as a retail
     * product (e.g. a 1,000 ml bottle for ₱150). `selling_price` stays the per-unit price the order
     * flow already charges; it is derived from these settings, so it needs more than two decimals.
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('selling_price', 12, 4)->nullable()->change();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->boolean('addon_enabled')->default(false)->after('selling_price');
            $table->string('addon_name', 100)->nullable()->after('addon_enabled');
            $table->unsignedInteger('addon_quantity')->nullable()->after('addon_name');
            $table->decimal('addon_price', 10, 2)->nullable()->after('addon_quantity');
            $table->boolean('retail_enabled')->default(false)->after('addon_price');
            $table->string('retail_name', 100)->nullable()->after('retail_enabled');
            $table->unsignedInteger('retail_pack_size')->nullable()->after('retail_name');
            $table->decimal('retail_price', 10, 2)->nullable()->after('retail_pack_size');
            $table->text('notes')->nullable()->after('retail_price');
        });

        // Items that already carry a selling price become a one-unit retail product, so editing
        // them does not lose that price.
        DB::table('inventory')
            ->whereNotNull('selling_price')
            ->update([
                'retail_enabled' => true,
                'retail_name' => DB::raw('name'),
                'retail_pack_size' => 1,
                'retail_price' => DB::raw('selling_price'),
            ]);
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn([
                'addon_enabled', 'addon_name', 'addon_quantity', 'addon_price',
                'retail_enabled', 'retail_name', 'retail_pack_size', 'retail_price', 'notes',
            ]);
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('selling_price', 10, 2)->nullable()->change();
        });
    }
};
