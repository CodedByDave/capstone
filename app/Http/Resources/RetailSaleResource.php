<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A retail product sale as a row of the Retail Products table.
 *
 * @mixin \App\Models\ShopOrder
 */
class RetailSaleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'payment_public_id' => $this->payment_public_id,
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'source' => $this->order_source,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total_amount' => (float) $this->total_amount,
            'items' => $this->supplies->pluck('pivot.label')->filter()->values()->all(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
