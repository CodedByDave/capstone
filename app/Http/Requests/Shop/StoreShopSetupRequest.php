<?php

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'offers_pickup' => $this->boolean('offers_pickup'),
            'offers_delivery' => $this->boolean('offers_delivery'),
        ]);
    }

    public function rules(): array
    {
        return [
            'location_mode' => ['required', Rule::in([
                Shop::LOCATION_SINGLE,
                Shop::LOCATION_MULTIPLE,
            ])],
            'offers_pickup' => ['required', 'boolean'],
            'offers_delivery' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_mode.required' => 'Choose whether your shop has one or multiple locations.',
        ];
    }
}
