<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            // A rider is now an employee whose position is "Driver"; legacy riders stay unlinked.
            $table->foreignId('employee_id')->nullable()->unique()->after('shop_id')
                ->constrained()->nullOnDelete();
            $table->boolean('is_online')->default(false)->after('status');
            // Since when the rider has been free; the rider waiting longest gets the next order.
            $table->timestamp('available_since')->nullable()->after('is_online');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropColumn(['is_online', 'available_since']);
        });
    }
};
