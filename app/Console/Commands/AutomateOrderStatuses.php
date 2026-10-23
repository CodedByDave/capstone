<?php

namespace App\Console\Commands;

use App\Services\OrderStatusAutomationService;
use Illuminate\Console\Command;

class AutomateOrderStatuses extends Command
{
    protected $signature = 'orders:automate';

    protected $description = 'Complete in-progress orders that are past their due time and remind shops about overdue orders, for shops on a paid plan with those rules switched on';

    public function handle(OrderStatusAutomationService $automation): int
    {
        $result = $automation->runScheduled();

        $this->info(sprintf(
            'Checked %d shop(s): completed %d order(s), reminded about %d.',
            $result['shops'],
            $result['completed'],
            $result['reminded'],
        ));

        return self::SUCCESS;
    }
}
