<?php

namespace App\Events;

use App\Models\ShopOrder;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/** The shop entered the actual weight of an order for the first time. */
class OrderWeightConfirmed implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly ShopOrder $order) {}
}
