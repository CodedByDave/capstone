<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('location_mode', 20)->nullable()->after('branch_name');
            $table->boolean('offers_pickup')->nullable()->after('location_mode');
            $table->boolean('offers_delivery')->nullable()->after('offers_pickup');
            $table->timestamp('setup_completed_at')->nullable()->after('offers_delivery');
        });

        // Preserve the capabilities existing shops had before this feature.
        DB::table('shops')->update([
            'location_mode' => 'multiple',
            'offers_pickup' => true,
            'offers_delivery' => true,
            'setup_completed_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'location_mode',
                'offers_pickup',
                'offers_delivery',
                'setup_completed_at',
            ]);
        });
    }
};
