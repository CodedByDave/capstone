<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_categories_id' => ['nullable', 'exists:inventory_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['required', 'string', 'max:50'],
            'package_unit' => ['nullable', 'string', 'max:50', 'required_with:package_size'],
            'package_size' => ['nullable', 'integer', 'min:1', 'required_with:package_unit'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0', 'gt:min_stock'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'selling_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_stock.gt' => 'Max stock must be greater than min stock.',
        ];
    }
}
