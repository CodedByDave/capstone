<?php

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use App\Rules\PhilippineMobileNumber;
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
            'branches' => $this->input('location_mode') === Shop::LOCATION_MULTIPLE
                ? array_values($this->all('branches')['branches'] ?? [])
                : [],
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
            'ownership_type' => ['required', Rule::in([
                Shop::OWNERSHIP_INDEPENDENT,
                Shop::OWNERSHIP_COMPANY,
                Shop::OWNERSHIP_FRANCHISEE,
            ])],
            'franchise_brand_name' => ['nullable', 'required_if:ownership_type,'.Shop::OWNERSHIP_FRANCHISEE, 'string', 'max:255'],
            'franchisor_name' => ['nullable', 'required_if:ownership_type,'.Shop::OWNERSHIP_FRANCHISEE, 'string', 'max:255'],
            'branches' => ['nullable', 'array', 'max:20'],
            'branches.*.name' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
            'branches.*.phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            'branches.*.block_street' => ['required', 'string', 'max:255'],
            'branches.*.municipality' => ['required', 'string', 'max:255'],
            'branches.*.barangay' => ['required', 'string', 'max:255'],
            'branches.*.postal_code' => ['required', 'string', 'max:10'],
            'branches.*.mayors_permit_number' => ['required', 'string', 'max:100'],
            'branches.*.mayors_permit' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'branches.*.mayors_expiry_date' => ['required', 'date', 'after:today'],
            'branches.*.sanitary_permit_number' => ['nullable', 'string', 'max:100'],
            'branches.*.sanitary_permit' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'branches.*.sanitary_expiry_date' => ['nullable', 'date', 'after:today', 'required_with:branches.*.sanitary_permit'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_mode.required' => 'Choose whether your shop has one or multiple locations.',
            'ownership_type.required' => 'Choose how this shop is owned and operated.',
            'franchise_brand_name.required_if' => 'Enter the franchise brand name.',
            'franchisor_name.required_if' => 'Enter the franchisor or franchise company name.',
            'branches.*.mayors_permit.required' => "Upload a separate Mayor's permit for every branch.",
            'branches.*.mayors_expiry_date.after' => "Each Mayor's permit expiry date must be in the future.",
        ];
    }
}
