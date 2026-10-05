<?php

namespace App\Repositories;

use App\Models\CustomerAgreementAcceptance;
use App\Models\ShopOrder;

class CustomerAgreementRepository
{
    public function createAcceptance(array $data): CustomerAgreementAcceptance
    {
        return CustomerAgreementAcceptance::query()->create($data);
    }

    public function forOrder(ShopOrder $order): ?CustomerAgreementAcceptance
    {
        return CustomerAgreementAcceptance::query()
            ->where('shop_order_id', $order->id)
            ->first();
    }
}
