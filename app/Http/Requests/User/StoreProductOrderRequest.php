<?php

namespace App\Http\Requests\User;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', Rule::exists('shops', 'id')->where('status', 'active')],
            // The order service checks the shop really sells each product and has it in stock.
            'retail_items' => ['required', 'array', 'min:1', 'max:20'],
            'retail_items.*.id' => ['required', 'integer'],
            'retail_items.*.count' => ['required', 'integer', 'min:1', 'max:10'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'retail_items.required' => 'Add at least one product to your order.',
            'retail_items.min' => 'Add at least one product to your order.',
        ];
    }
}
