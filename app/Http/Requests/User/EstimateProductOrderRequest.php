<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateProductOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', Rule::exists('shops', 'id')->where('status', 'active')],
            // The order service checks the shop really sells each product.
            'retail_items' => ['required', 'array', 'min:1', 'max:20'],
            'retail_items.*.id' => ['required', 'integer'],
            'retail_items.*.count' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
