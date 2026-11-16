<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Each subscription keeps its own copy of the module names it bought.
        DB::table('order_modules')->where('name', 'HRM')->update(['name' => 'Staff Management']);
    }

    public function down(): void
    {
        DB::table('order_modules')->where('name', 'Staff Management')->update(['name' => 'HRM']);
    }
};
