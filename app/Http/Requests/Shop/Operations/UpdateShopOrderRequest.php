<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // In UpdateShopOrderRequest
    public function rules(): array
    {
        return [
            'customer_name'          => ['required', 'string', 'max:255'],
            'customer_phone'         => ['nullable', 'string', 'max:20'],
            'customer_address'       => ['nullable', 'string', 'max:500'],
            'service_id'             => ['required', 'integer', Rule::exists('services_and_pricing', 'id')],
            'pickup_type'            => ['required', Rule::in(ShopOrder::PICKUP_TYPES)],
            'pricing_model'          => ['required', Rule::in(['per_kg', 'per_bundle'])],
            'estimated_weight_kg'    => [
                Rule::requiredIf(fn() => $this->input('pricing_model') === 'per_kg'),
                'nullable',
                'numeric',
                'min:0.1',
            ],
            'actual_weight_kg'       => ['nullable', 'numeric', 'min:0.1'],
            'price_per_kg'           => ['nullable', 'numeric', 'min:0'],
            'bundle_weight_kg'       => [
                Rule::requiredIf(fn() => $this->input('pricing_model') === 'per_bundle'),
                'nullable',
                'numeric',
                'min:0.1',
            ],
            'bundle_price'           => [
                Rule::requiredIf(fn() => $this->input('pricing_model') === 'per_bundle'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'bundle_quantity'        => [
                Rule::requiredIf(fn() => $this->input('pricing_model') === 'per_bundle'),
                'nullable',
                'integer',
                'min:1',
            ],
            'promotion_id'           => ['nullable', 'integer', 'exists:promotions,id'],
            'additional_charges'     => ['nullable', 'numeric', 'min:0'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'total_amount'           => ['required', 'numeric', 'min:0'],
            'payment_method'         => ['required', Rule::in(ShopOrder::PAYMENT_METHODS)],
            'payment_status'         => ['required', Rule::in(ShopOrder::PAYMENT_STATUSES)],  // ← add
            'amount_paid'            => ['nullable', 'numeric', 'min:0'],
            'status'                 => ['required', Rule::in(ShopOrder::STATUSES)],           // ← add
            'estimated_completion_at' => ['nullable', 'date'],
            'supplies'               => ['nullable', 'array'],
            'supplies.*.inventory_id' => ['required', 'integer', 'exists:inventory,id'],
            'supplies.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
            'supplies.*.unit'        => ['required', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'additional_charges' => $this->additional_charges ?? 0,
            'discount_amount'    => $this->discount_amount ?? 0,
            'amount_paid'        => $this->amount_paid ?? 0,
            'price_per_kg'       => $this->price_per_kg ?? 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'service_id.exists'                     => 'The selected service does not exist.',
            'supplies.*.inventory_id.exists'        => 'One or more selected supplies do not exist in inventory.',
            'supplies.*.quantity_used.min'          => 'Quantity used must be at least 0.01.',
        ];
    }
}
