<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('permit_expiry_date', 'mayors_expiry_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->date('dti_expiry_date')->nullable()->after('mayors_expiry_date');
            $table->date('sanitary_expiry_date')->nullable()->after('dti_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['dti_expiry_date', 'sanitary_expiry_date']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('mayors_expiry_date', 'permit_expiry_date');
        });
    }
};
