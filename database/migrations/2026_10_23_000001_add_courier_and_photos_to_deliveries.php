<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('courier', 20)->default('own_rider')->after('rider_id');
            $table->string('courier_reference', 100)->nullable()->after('courier');
            $table->string('courier_tracking_url', 500)->nullable()->after('courier_reference');
            $table->string('courier_driver_name', 100)->nullable()->after('courier_tracking_url');
            $table->string('courier_driver_phone', 20)->nullable()->after('courier_driver_name');
            $table->string('courier_vehicle_plate', 20)->nullable()->after('courier_driver_phone');
            $table->decimal('courier_cost', 10, 2)->nullable()->after('courier_vehicle_plate');
        });

        Schema::create('delivery_photos', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_photos');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'courier', 'courier_reference', 'courier_tracking_url', 'courier_driver_name',
                'courier_driver_phone', 'courier_vehicle_plate', 'courier_cost',
            ]);
        });
    }
};
