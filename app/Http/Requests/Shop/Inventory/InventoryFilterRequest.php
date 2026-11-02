<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryFilterRequest extends FormRequest
{
    /** Columns the inventory list can be sorted by (the table's sortable headers). */
    public const SORTABLE = ['name', 'category', 'supplier', 'quantity', 'unit_price', 'status'];

    public function authorize(): bool
    {
        // Who may open the list is decided by the route (owner, or staff with Inventory view).
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'supplier_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'stock' => ['nullable', Rule::in(['low'])],
            'sort_by' => ['nullable', Rule::in(self::SORTABLE)],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * The filters that were given, with the sort filled in (name, A to Z, by default).
     *
     * @return array<string, string>
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            ...array_filter(
                collect($validated)->only(['search', 'category_id', 'supplier_id', 'status', 'stock'])->all(),
                fn ($value) => $value !== null && $value !== '',
            ),
            'sort_by' => $validated['sort_by'] ?? 'name',
            'sort_direction' => $validated['sort_direction'] ?? 'asc',
        ];
    }
}
