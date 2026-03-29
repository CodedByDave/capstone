<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('kyc_bir')->nullable()->after('postal_code');
            $table->string('kyc_dti')->nullable()->after('kyc_bir');
            $table->string('kyc_mayors')->nullable()->after('kyc_dti');
            $table->string('kyc_sanitary')->nullable()->after('kyc_mayors');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['kyc_bir', 'kyc_dti', 'kyc_mayors', 'kyc_sanitary']);
        });
    }
};
