<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shops')
            ->select(['id', 'last_activity_at'])
            ->orderBy('id')
            ->eachById(function (object $shop): void {
                $latestActivity = DB::table('activity_logs')
                    ->where('shop_id', $shop->id)
                    ->max('created_at');

                if ($latestActivity !== null && (
                    $shop->last_activity_at === null ||
                    $latestActivity > $shop->last_activity_at
                )) {
                    DB::table('shops')
                        ->where('id', $shop->id)
                        ->update(['last_activity_at' => $latestActivity]);
                }
            });
    }

    public function down(): void
    {
        // Backfilled timestamps cannot be distinguished from live activity.
    }
};
