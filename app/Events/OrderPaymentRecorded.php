<?php

namespace App\Events;

use App\Models\ShopOrder;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/** A payment was confirmed on an order, at the shop or online. */
class OrderPaymentRecorded implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly ShopOrder $order) {}
}
