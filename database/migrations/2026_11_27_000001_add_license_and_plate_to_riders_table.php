<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('plate_number', 12)->nullable()->after('vehicle_type');
            // Encrypted at rest on the private disk; never served from a public URL.
            $table->string('license_photo_path')->nullable()->after('plate_number');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['plate_number', 'license_photo_path']);
        });
    }
};
