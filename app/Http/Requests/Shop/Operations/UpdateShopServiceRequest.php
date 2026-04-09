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
            'estimated_hours'  => ['nullable', 'integer', 'min:1'],
            'price_per_kg'     => [
                Rule::requiredIf(fn() => $this->pricing_model === 'per_kg'),
                'nullable', 'numeric', 'min:0',
            ],
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
}
