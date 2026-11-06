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
            'package_unit' => ['required', 'string', 'max:50'],
            'package_size' => ['required', 'integer', 'min:1'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0', 'gt:min_stock'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99999999'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'package_unit.required' => 'Choose the purchase unit — the cost price is entered per purchase unit.',
            'package_size.required' => 'Enter how much one purchase unit holds.',
            'max_stock.gt' => 'Max stock must be greater than min stock.',
            'photo.image' => 'The item photo must be an image.',
            'photo.mimes' => 'The item photo must be a JPG, PNG, or WebP image.',
            'photo.max' => 'The item photo must be 5 MB or smaller.',
        ];
    }
}
