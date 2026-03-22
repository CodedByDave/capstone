<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $inventoryId = $this->route('inventory')?->id ?? $this->route('inventory');

        return [
            'inventory_categories_id' => ['nullable', 'exists:inventory_categories,id'],
            'supplier_id'             => ['nullable', 'exists:suppliers,id'],
            'name'                    => ['required', 'string', 'max:255'],
            'sku'                     => ['required', 'string', 'max:100', Rule::unique('inventory', 'sku')->ignore($inventoryId)],
            'description'             => ['nullable', 'string', 'max:2000'],
            'unit'                    => ['required', 'string', 'max:50'],
            'min_stock'               => ['required', 'integer', 'min:0'],
            'max_stock'               => ['nullable', 'integer', 'min:0', 'gt:min_stock'],
            'unit_price'              => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'selling_price'           => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'status'                  => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique'   => 'This SKU is already used by another inventory item.',
            'max_stock.gt' => 'Max stock must be greater than min stock.',
        ];
    }
}
