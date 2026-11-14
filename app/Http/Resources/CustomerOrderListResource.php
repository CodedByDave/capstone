<?php

namespace App\Http\Resources;

use App\Models\ShopOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrder */
class CustomerOrderListResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_public_id' => $this->payment_public_id,
            'order_number' => $this->order_number,
            'shop' => $this->shop
                ? ['name' => $this->shop->shop_name, 'location' => "{$this->shop->barangay}, {$this->shop->municipality}"]
                : null,
            'service_name' => $this->isProductOrder() ? 'Products' : ($this->service->service_name ?? '—'),
            'order_type' => $this->order_type,
            'status' => $this->status,
            'is_rush' => (bool) $this->is_rush,
            'payment_status' => $this->payment_status,
            'total_amount' => (float) $this->total_amount,
            'order_source' => $this->order_source,
            'pickup_type' => $this->pickup_type,
            'created_at' => $this->created_at->toDateString(),
        ];
    }
}
