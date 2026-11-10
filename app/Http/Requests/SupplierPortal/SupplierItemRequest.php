<?php

namespace App\Http\Requests\SupplierPortal;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;

/** A supply the supplier adds to or edits in their catalog. */
class SupplierItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::Supplier->value;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'unit' => is_string($this->input('unit')) ? trim($this->input('unit')) : $this->input('unit'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'stock_quantity' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the name of the supply.',
            'stock_quantity.required' => 'Enter how many you have in stock.',
            'unit_price.required' => 'Enter the price you sell it at.',
        ];
    }
}
