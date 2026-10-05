<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_agreement_platform_signatures', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_agreement_acceptance_id');
            $table->unique(
                'business_agreement_acceptance_id',
                'agreement_platform_acceptance_unique',
            );
            $table->foreign(
                'business_agreement_acceptance_id',
                'agreement_platform_acceptance_fk',
            )->references('id')
                ->on('business_agreement_acceptances')
                ->restrictOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signer_name');
            $table->string('signer_role');
            $table->string('signature_method');
            $table->string('signature_path');
            $table->timestamp('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_agreement_platform_signatures');
    }
};
