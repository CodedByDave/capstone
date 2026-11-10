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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->unique()->after('id');
            $table->foreignId('user_id')->nullable()->unique()->after('shop_id')
                ->constrained('users')->nullOnDelete();
        });

        DB::table('suppliers')
            ->whereNull('public_id')
            ->orderBy('id')
            ->eachById(function (object $supplier): void {
                DB::table('suppliers')
                    ->where('id', $supplier->id)
                    ->update(['public_id' => (string) Str::ulid()]);
            });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
