<?php

namespace App\Http\Requests\Shop\Operations;

use App\Http\Requests\Shop\Operations\Concerns\ValidatesServiceCatalogue;
use App\Models\ShopService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopServiceRequest extends FormRequest
{
    use ValidatesServiceCatalogue;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->catalogueRules(),
            'service_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'pricing_model' => ['required', Rule::in(ShopService::PRICING_MODELS)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photo' => ['boolean'],
            'price_per_kg' => [
                Rule::requiredIf(fn () => $this->pricing_model === 'per_kg'),
                'nullable', 'numeric', 'min:1', 'max:9999',
            ],
            'price_per_piece' => [
                Rule::requiredIf(fn () => $this->pricing_model === 'per_piece'),
                'nullable', 'numeric', 'min:1', 'max:9999',
            ],

            'bundle_price' => [
                Rule::requiredIf(fn () => $this->pricing_model === 'per_bundle'),
                'nullable', 'numeric', 'min:1', 'max:99999',
            ],
            'bundle_weight_kg' => [
                Rule::requiredIf(fn () => $this->pricing_model === 'per_bundle'),
                'nullable', 'numeric', 'min:0.1', 'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...$this->catalogueMessages(),
            'category.required' => 'Please select a service category.',
            'category.in' => 'Please select a valid service category.',
            'price_per_kg.min' => 'Price per kg must be at least ₱1.',
            'price_per_kg.max' => 'Price per kg cannot exceed ₱9,999.',
            'price_per_piece.required_if' => 'Price per piece is required for per-piece pricing.',
            'price_per_piece.min' => 'Price per piece must be at least ₱1.',
            'price_per_piece.max' => 'Price per piece cannot exceed ₱9,999.',
            'bundle_price.min' => 'Bundle price must be at least ₱1.',
            'bundle_price.max' => 'Bundle price cannot exceed ₱99,999.',
            'bundle_weight_kg.max' => 'Bundle weight cannot exceed 500 kg.',
            'photo.image' => 'The service photo must be an image.',
            'photo.mimes' => 'The service photo must be a JPG, PNG, or WebP image.',
            'photo.max' => 'The service photo must be 5 MB or smaller.',
        ];
    }
}
