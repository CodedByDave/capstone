<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Orders no longer book their supplies as an expense: the restock already did, so these rows
     * counted the same stock twice. Archived ones go too, so they cannot be restored into the totals.
     */
    public function up(): void
    {
        DB::table('expenses')->where('source', 'order_supplies')->delete();
    }

    /** The removed rows cannot be rebuilt, and the code no longer creates them. */
    public function down(): void {}
};
