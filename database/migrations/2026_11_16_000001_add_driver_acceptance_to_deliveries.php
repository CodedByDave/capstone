<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // When the assigned rider accepted the run; null while it still awaits their answer.
            $table->timestamp('accepted_at')->nullable()->after('assigned_at');
        });

        Schema::create('delivery_rejections', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 40);
            $table->string('note', 500)->nullable();
            // Proof of the reason (e.g. the flat tyre), kept on the private disk.
            $table->string('photo_path');
            $table->timestamps();

            $table->index(['delivery_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rejections');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('accepted_at');
        });
    }
};
