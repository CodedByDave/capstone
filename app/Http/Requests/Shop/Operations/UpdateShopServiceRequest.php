<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopService;;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopServiceRequest extends FormRequest
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
            'estimated_hours'  => ['nullable', 'integer', 'min:1', 'max:720'],
            'price_per_kg'     => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_kg'),
                'nullable', 'numeric', 'min:1', 'max:9999',
            ],
            'bundle_price'     => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_bundle'),
                'nullable', 'numeric', 'min:1', 'max:99999',
            ],
            'bundle_weight_kg' => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_bundle'),
                'nullable', 'numeric', 'min:0.1', 'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price_per_kg.min'     => 'Price per kg must be at least ₱1.',
            'price_per_kg.max'     => 'Price per kg cannot exceed ₱9,999.',
            'bundle_price.min'     => 'Bundle price must be at least ₱1.',
            'bundle_price.max'     => 'Bundle price cannot exceed ₱99,999.',
            'bundle_weight_kg.max' => 'Bundle weight cannot exceed 500 kg.',
            'estimated_hours.max'  => 'Estimated time cannot exceed 720 hours (30 days).',
        ];
    }
}
