<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMIT_COLUMNS = [
        'bir_expiry_date',
        'dti_expiry_date',
        'mayors_expiry_date',
        'sanitary_expiry_date',
    ];

    public function up(): void
    {
        DB::table('shops')
            ->select(['id', 'owner_id', ...self::PERMIT_COLUMNS])
            ->orderBy('id')
            ->eachById(function (object $shop): void {
                $order = DB::table('orders')
                    ->where('user_id', $shop->owner_id)
                    ->whereIn('status', ['approved', 'paid'])
                    ->where(function ($query): void {
                        foreach (self::PERMIT_COLUMNS as $column) {
                            $query->orWhereNotNull($column);
                        }
                    })
                    ->latest('created_at')
                    ->latest('id')
                    ->first(self::PERMIT_COLUMNS);

                if (! $order) {
                    return;
                }

                $updates = [];
                foreach (self::PERMIT_COLUMNS as $column) {
                    if ($shop->{$column} === null && $order->{$column} !== null) {
                        $updates[$column] = $order->{$column};
                    }
                }

                if ($updates !== []) {
                    DB::table('shops')->where('id', $shop->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        // The original null values cannot be distinguished from dates edited later.
    }
};
