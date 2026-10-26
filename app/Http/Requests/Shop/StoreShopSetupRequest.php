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
            'ownership_type' => $this->input('ownership_type', Shop::OWNERSHIP_INDEPENDENT),
        ]);
    }

    public function rules(): array
    {
        return [
            'offers_pickup' => ['required', 'boolean'],
            'offers_delivery' => ['required', 'boolean'],
            'ownership_type' => ['required', Rule::in([
                Shop::OWNERSHIP_INDEPENDENT,
                Shop::OWNERSHIP_COMPANY,
                Shop::OWNERSHIP_FRANCHISEE,
            ])],
            'franchise_brand_name' => ['nullable', 'required_if:ownership_type,'.Shop::OWNERSHIP_FRANCHISEE, 'string', 'max:255'],
            'franchisor_name' => ['nullable', 'required_if:ownership_type,'.Shop::OWNERSHIP_FRANCHISEE, 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ownership_type.required' => 'Choose how this shop is owned and operated.',
            'franchise_brand_name.required_if' => 'Enter the franchise brand name.',
            'franchisor_name.required_if' => 'Enter the franchisor or franchise company name.',
        ];
    }
}
