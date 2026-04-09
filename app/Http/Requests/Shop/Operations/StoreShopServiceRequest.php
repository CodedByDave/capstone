<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_name'     => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'is_active'        => ['boolean'],
            'pricing_model'    => ['required', Rule::in(ShopService::PRICING_MODELS)],
            'estimated_hours'  => ['nullable', 'integer', 'min:1'],

            // per_kg
            'price_per_kg'     => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_kg'),
                'nullable', 'numeric', 'min:0',
            ],

            // per_bundle
            'bundle_price'     => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_bundle'),
                'nullable', 'numeric', 'min:0',
            ],
            'bundle_weight_kg' => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_bundle'),
                'nullable', 'numeric', 'min:0.1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price_per_kg.required_if'     => 'Price per kg is required for per-kg pricing.',
            'bundle_price.required_if'     => 'Bundle price is required for bundle pricing.',
            'bundle_weight_kg.required_if' => 'Bundle weight is required for bundle pricing.',
        ];
    }
}
