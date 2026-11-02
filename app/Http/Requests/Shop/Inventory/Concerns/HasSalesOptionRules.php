<?php

namespace App\Http\Requests\Shop\Inventory\Concerns;

use Illuminate\Validation\Rule;

/**
 * Validation for how an inventory item is charged to customers: as an add-on and/or as a retail product.
 * The per-unit selling price is derived from these in InventoryService, never typed in.
 */
trait HasSalesOptionRules
{
    /** @return array<string, array<int, mixed>> */
    protected function salesOptionRules(): array
    {
        $addon = fn () => $this->boolean('addon_enabled');
        $retail = fn () => $this->boolean('retail_enabled');

        return [
            'addon_enabled' => ['nullable', 'boolean'],
            'addon_name' => [Rule::requiredIf($addon), 'nullable', 'string', 'max:100'],
            'addon_quantity' => [Rule::requiredIf($addon), 'nullable', 'integer', 'min:1', 'max:100000000'],
            'addon_price' => [Rule::requiredIf($addon), 'nullable', 'numeric', 'min:0.01', 'max:9999999.99'],
            'retail_enabled' => ['nullable', 'boolean'],
            'retail_name' => [Rule::requiredIf($retail), 'nullable', 'string', 'max:100'],
            'retail_pack_size' => [Rule::requiredIf($retail), 'nullable', 'integer', 'min:1', 'max:100000000'],
            'retail_price' => [Rule::requiredIf($retail), 'nullable', 'numeric', 'min:0.01', 'max:9999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
