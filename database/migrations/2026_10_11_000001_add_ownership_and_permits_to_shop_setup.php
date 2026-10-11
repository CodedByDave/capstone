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
            $table->string('ownership_type', 30)->nullable()->after('branch_name');
            $table->string('franchise_brand_name')->nullable()->after('ownership_type');
            $table->string('franchisor_name')->nullable()->after('franchise_brand_name');
        });

        DB::table('shops')->whereNull('ownership_type')->update([
            'ownership_type' => 'independent',
        ]);

        Schema::table('branches', function (Blueprint $table) {
            $table->string('block_street')->nullable()->after('address');
            $table->string('municipality')->nullable()->after('block_street');
            $table->string('barangay')->nullable()->after('municipality');
            $table->string('postal_code', 10)->nullable()->after('barangay');
            $table->string('mayors_permit_number')->nullable()->after('postal_code');
            $table->string('mayors_permit_path')->nullable()->after('mayors_permit_number');
            $table->date('mayors_expiry_date')->nullable()->after('mayors_permit_path');
            $table->string('sanitary_permit_number')->nullable()->after('mayors_expiry_date');
            $table->string('sanitary_permit_path')->nullable()->after('sanitary_permit_number');
            $table->date('sanitary_expiry_date')->nullable()->after('sanitary_permit_path');
            $table->string('permit_status', 20)->default('missing')->after('sanitary_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'block_street', 'municipality', 'barangay', 'postal_code',
                'mayors_permit_number', 'mayors_permit_path', 'mayors_expiry_date',
                'sanitary_permit_number', 'sanitary_permit_path', 'sanitary_expiry_date',
                'permit_status',
            ]);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['ownership_type', 'franchise_brand_name', 'franchisor_name']);
        });
    }
};
