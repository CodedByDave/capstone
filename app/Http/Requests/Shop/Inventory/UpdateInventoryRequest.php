<?php

namespace App\Http\Requests\Shop\Inventory;

use App\Http\Requests\Shop\Inventory\Concerns\HasSalesOptionRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    use HasSalesOptionRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inventoryId = $this->route('inventory')?->id ?? $this->route('inventory');

        return $this->salesOptionRules() + [
            'inventory_categories_id' => ['nullable', 'exists:inventory_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
            'unit' => ['required', 'string', 'max:50'],
            'package_unit' => ['nullable', 'string', 'max:50', 'required_with:package_size'],
            'package_size' => ['nullable', 'integer', 'min:1', 'required_with:package_unit'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0', 'gt:min_stock'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_stock.gt' => 'Max stock must be greater than min stock.',
            'photo.image' => 'The item photo must be an image.',
            'photo.mimes' => 'The item photo must be a JPG, PNG, or WebP image.',
            'photo.max' => 'The item photo must be 5 MB or smaller.',
        ];
    }
}
