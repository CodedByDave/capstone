<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class BackfillLowStockAlerts extends Command
{
    protected $signature = 'inventory:backfill-low-stock-alerts';

    protected $description = 'Create a low stock alert for every active item already at or below its minimum that has never had one';

    public function handle(InventoryService $inventory): int
    {
        $this->info(sprintf('Created %d low stock alert(s).', $inventory->backfillLowStockAlerts()));

        return self::SUCCESS;
    }
}
