<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_agreements', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('title');
            $table->string('version')->unique();
            $table->longText('content');
            $table->char('content_hash', 64);
            $table->timestamp('effective_at');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('business_agreement_acceptances', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_agreement_id')
                ->constrained('business_agreements')
                ->restrictOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->string('signer_name');
            $table->string('signer_role');
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->char('content_hash', 64);
            $table->timestamps();

            $table->unique(
                ['business_agreement_id', 'shop_id'],
                'business_agreement_shop_unique',
            );
        });

        $content = (string) config('business_agreement.content');
        $now = now();

        DB::table('business_agreements')->insert([
            'public_id' => (string) Str::ulid(),
            'title' => config('business_agreement.title'),
            'version' => config('business_agreement.version'),
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'effective_at' => config('business_agreement.effective_at'),
            'published_at' => $now,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_agreement_acceptances');
        Schema::dropIfExists('business_agreements');
    }
};
