<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class EstimateOrderBreakdownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', $this->ownShopRule()],
            'service_id' => ['required', 'integer'],
            'estimated_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'piece_count' => ['nullable', 'integer', 'min:1', 'max:500'],
            'additional_items' => ['nullable', 'array', 'max:20'],
            'additional_items.*.id' => ['required', 'integer'],
            'additional_items.*.count' => ['required', 'integer', 'min:1', 'max:10'],
            'promotion_id' => ['nullable', 'integer', Rule::exists('promotions', 'id')->where('shop_id', $this->user()?->referred_shop_id)],
        ];
    }

    /** Customers belong to the shop whose QR they joined through and can only order from it. */
    private function ownShopRule(): In
    {
        return Rule::in(array_filter([$this->user()?->referred_shop_id]));
    }
}
