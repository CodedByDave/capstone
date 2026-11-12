<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Whether a rider drives a shop vehicle or their own; only their own needs plate and license on file. */
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('vehicle_ownership', 10)->default('shop')->after('vehicle_type');
        });

        // Riders who already have a plate or license recorded are using their own vehicle.
        DB::table('riders')
            ->where(fn ($q) => $q->whereNotNull('plate_number')->orWhereNotNull('license_photo_path'))
            ->update(['vehicle_ownership' => 'own']);
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn('vehicle_ownership');
        });
    }
};
