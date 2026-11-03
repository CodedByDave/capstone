<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();

            // What the owner typed at registration, frozen at submission time.
            $table->string('shop_name');
            $table->string('phone')->nullable();
            $table->string('block_street')->nullable();
            $table->string('municipality');
            $table->string('barangay');
            $table->string('postal_code', 10);

            $table->string('kyc_bir')->nullable();
            $table->string('kyc_dti')->nullable();
            $table->string('kyc_mayors')->nullable();
            $table->string('kyc_sanitary')->nullable();
            $table->date('bir_expiry_date')->nullable();
            $table->date('dti_expiry_date')->nullable();
            $table->date('mayors_expiry_date')->nullable();
            $table->date('sanitary_expiry_date')->nullable();

            $table->json('kyc_ocr_data')->nullable();
            $table->json('kyc_confirmed_data')->nullable();
            $table->json('kyc_flags')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
