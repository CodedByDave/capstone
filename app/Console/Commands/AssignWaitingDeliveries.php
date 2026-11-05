<?php

namespace App\Console\Commands;

use App\Services\LogisticsService;
use Illuminate\Console\Command;

class AssignWaitingDeliveries extends Command
{
    protected $signature = 'deliveries:assign-waiting';

    protected $description = 'Give deliveries that are waiting for a rider to any rider who is free now';

    public function handle(LogisticsService $logistics): int
    {
        $this->info(sprintf('Assigned %d waiting deliveries.', $logistics->assignAllWaitingDeliveries()));

        return self::SUCCESS;
    }
}
