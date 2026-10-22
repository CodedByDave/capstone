<?php

namespace App\Http\Resources;

use App\Models\ShopOrderPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrderPayment */
class ShopOrderPaymentResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'amount' => (float) $this->amount,
            'method' => $this->method,
            'reference' => $this->reference,
            'note' => $this->note,
            'status' => $this->status,
            'source' => $this->source,
            'recorded_by' => $this->recorder?->name,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'voided_at' => $this->voided_at?->toIso8601String(),
            'voided_by' => $this->voider?->name,
            'void_reason' => $this->void_reason,
        ];
    }
}
