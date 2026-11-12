<?php

use App\Models\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Restock expenses used to note the stock unit and its cost ("30000 ml at ₱0.0533 per ml"). The
 * note now counts what was bought in the item's packages ("10 bottles"); the amount already says
 * what it cost. Expenses whose item can no longer be found keep just the quantity ("30,000 ml").
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('expenses')
            ->where('title', 'like', 'Restock: %')
            ->whereNotNull('description')
            ->select(['id', 'shop_id', 'title', 'description'])
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    // "30000 ml at ₱0.0533 per ml" or "30000 ml"
                    if (! preg_match('/^([0-9][0-9,]*) (\S+)(?: at ₱[0-9.,]+ per \S+)?$/u', $row->description, $m)) {
                        continue;
                    }

                    $quantity = (int) str_replace(',', '', $m[1]);
                    $item = DB::table('inventory')
                        ->where('shop_id', $row->shop_id)
                        ->where('name', substr($row->title, strlen('Restock: ')))
                        ->where('unit', $m[2])
                        ->orderByRaw('deleted_at is null desc')
                        ->first(['unit', 'package_unit', 'package_size']);

                    $description = (new Inventory([
                        'unit' => $m[2],
                        'package_unit' => $item?->package_unit,
                        'package_size' => $item?->package_size,
                    ]))->describeQuantity($quantity);

                    if ($description !== $row->description) {
                        DB::table('expenses')->where('id', $row->id)->update(['description' => $description]);
                    }
                }
            });
    }

    public function down(): void
    {
        // The old wording is derived data (quantity and the item's unit cost); nothing to restore.
    }
};
