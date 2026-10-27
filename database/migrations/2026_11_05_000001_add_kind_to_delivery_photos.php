<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A delivery photo is now either proof of the delivery ("proof", what every existing photo is) or proof
     * of the cash the rider collected at the door ("cash").
     */
    public function up(): void
    {
        Schema::table('delivery_photos', function (Blueprint $table) {
            $table->string('kind', 20)->default('proof')->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_photos', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
