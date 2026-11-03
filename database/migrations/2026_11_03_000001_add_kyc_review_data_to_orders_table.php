<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // What OCR read from each uploaded document, per document key (bir, dti, ...).
            $table->json('kyc_ocr_data')->nullable();
            // TIN, registered name and registered address the shop confirmed after OCR.
            $table->json('kyc_confirmed_data')->nullable();
            // Fields/documents the admin flagged as wrong when rejecting.
            $table->json('kyc_flags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['kyc_ocr_data', 'kyc_confirmed_data', 'kyc_flags']);
        });
    }
};
