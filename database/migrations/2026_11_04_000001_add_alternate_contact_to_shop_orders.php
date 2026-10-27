<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A second person the rider can reach, or hand the laundry over to, when the customer is not
     * there or does not pick up the phone. Only used on delivery-pickup orders.
     */
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->string('alternate_contact_name', 150)->nullable()->after('customer_phone');
            $table->string('alternate_contact_phone', 20)->nullable()->after('alternate_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn(['alternate_contact_name', 'alternate_contact_phone']);
        });
    }
};
