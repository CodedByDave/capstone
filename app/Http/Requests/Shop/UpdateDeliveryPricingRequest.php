<?php

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        // Empty inputs from the form mean "not set"; an empty radius means unlimited.
        $this->merge(collect([
            'delivery_fixed_fee',
            'delivery_base_fee',
            'delivery_per_km_rate',
            'delivery_free_km',
            'delivery_min_fee',
            'delivery_max_km',
        ])->filter(fn (string $field) => $this->has($field) && $this->input($field) === '')
            ->mapWithKeys(fn (string $field) => [$field => null])
            ->all());
    }

    public function rules(): array
    {
        $perKm = 'required_if:delivery_fee_mode,'.Shop::DELIVERY_FEE_PER_KM;

        return [
            'delivery_fee_mode' => ['required', Rule::in(Shop::DELIVERY_FEE_MODES)],
            'delivery_fixed_fee' => ['nullable', 'required_if:delivery_fee_mode,'.Shop::DELIVERY_FEE_FIXED, 'numeric', 'min:0', 'max:5000'],
            'delivery_base_fee' => ['nullable', $perKm, 'numeric', 'min:0', 'max:5000'],
            'delivery_per_km_rate' => ['nullable', $perKm, 'numeric', 'min:0', 'max:1000'],
            'delivery_free_km' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delivery_min_fee' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'delivery_max_km' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_fee_mode.in' => 'Choose a fixed rate or per-kilometer pricing.',
            'delivery_fixed_fee.required_if' => 'Enter the fixed delivery fee.',
            'delivery_base_fee.required_if' => 'Enter the base fee for per-kilometer pricing.',
            'delivery_per_km_rate.required_if' => 'Enter the rate per kilometer.',
            'delivery_max_km.min' => 'The delivery radius must be at least 0.5 km, or leave it empty for no limit.',
        ];
    }
}
