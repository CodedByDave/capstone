<?php

namespace App\Console\Commands;

use App\Services\ShopOrderPaymentService;
use Illuminate\Console\Command;

class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Confirm online order payments that were paid but never reported back, and expire abandoned checkouts';

    public function handle(ShopOrderPaymentService $payments): int
    {
        $result = $payments->reconcileAllPending();

        $this->info(sprintf(
            'Confirmed %d, still pending %d, expired %d, could not check %d.',
            $result['confirmed'],
            $result['still_pending'],
            $result['expired'],
            $result['failed'],
        ));

        return self::SUCCESS;
    }
}
