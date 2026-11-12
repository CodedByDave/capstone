<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A service can show up to three photos. `photo_path` stays as the cover (the first photo) so the
 * places that show one picture keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->json('photo_paths')->nullable()->after('photo_path');
        });

        DB::table('services_and_pricing')->whereNotNull('photo_path')->orderBy('id')->each(function ($row) {
            DB::table('services_and_pricing')->where('id', $row->id)
                ->update(['photo_paths' => json_encode([$row->photo_path])]);
        });
    }

    public function down(): void
    {
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->dropColumn('photo_paths');
        });
    }
};
