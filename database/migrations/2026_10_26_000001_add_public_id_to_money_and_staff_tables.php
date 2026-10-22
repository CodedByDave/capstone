<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** Tables whose records appear in URLs and must not expose sequential ids. */
    private const TABLES = ['payrolls', 'payroll_items', 'expenses', 'expense_categories', 'employees'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            });

            DB::table($tableName)
                ->whereNull('public_id')
                ->orderBy('id')
                ->eachById(function (object $row) use ($tableName): void {
                    DB::table($tableName)
                        ->where('id', $row->id)
                        ->update(['public_id' => (string) Str::ulid()]);
                });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['public_id']);
                $table->dropColumn('public_id');
            });
        }
    }
};
