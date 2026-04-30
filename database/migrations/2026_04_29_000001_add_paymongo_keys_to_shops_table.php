<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->text('paymongo_secret_key')->nullable()->after('maya_qr');
            $table->string('paymongo_public_key')->nullable()->after('paymongo_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['paymongo_secret_key', 'paymongo_public_key']);
        });
    }
};
