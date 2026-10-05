<?php

use App\Support\PhilippinePhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'shops' => 'phone',
            'orders' => 'phone',
            'employees' => 'phone',
            'employee_archives' => 'phone',
            'branches' => 'phone',
            'suppliers' => 'phone',
            'riders' => 'phone',
            'shop_orders' => 'customer_phone',
            'deliveries' => 'customer_phone',
        ] as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->select(['id', $column])
                ->whereNotNull($column)
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $column) {
                    foreach ($rows as $row) {
                        $formatted = PhilippinePhone::format($row->{$column});

                        if ($formatted !== $row->{$column}) {
                            DB::table($table)
                                ->where('id', $row->id)
                                ->update([$column => $formatted]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Formatting is intentionally retained because it is lossless.
    }
};
