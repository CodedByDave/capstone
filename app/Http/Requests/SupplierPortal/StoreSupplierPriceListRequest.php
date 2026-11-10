<?php

namespace App\Http\Requests\SupplierPortal;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierPriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::Supplier->value;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Choose a photo of your price list.',
            'photo.mimes' => 'Upload the price list as a JPG, PNG or WebP photo.',
            'photo.max' => 'The photo must be 5 MB or smaller.',
        ];
    }
}
