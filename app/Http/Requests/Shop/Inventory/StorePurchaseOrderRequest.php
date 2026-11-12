<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/** A new purchase order. Which supplier and items belong to the shop is checked by the service. */
class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may order is decided by the route (owner, or staff with Inventory create).
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'send' => $this->boolean('send'),
            'notes' => is_string($this->notes) ? trim($this->notes) : $this->notes,
        ]);
    }

    public function rules(): array
    {
        return [
            'supplier' => ['required', 'string', 'ulid'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'send' => ['boolean'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.supplier_item' => ['required', 'string', 'ulid', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:100000', 'decimal:0,2'],
            'items.*.inventory_id' => ['nullable', 'integer'],
            'items.*.stock_per_unit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier.required' => 'Choose a supplier.',
            'items.required' => 'Add at least one supply to the order.',
            'items.min' => 'Add at least one supply to the order.',
            'items.*.supplier_item.distinct' => 'This supply is already on the order.',
            'items.*.quantity.required' => 'Enter a quantity.',
            'items.*.quantity.gt' => 'The quantity must be more than zero.',
            'items.*.stock_per_unit.min' => 'Each unit must add at least 1 to stock.',
        ];
    }
}
