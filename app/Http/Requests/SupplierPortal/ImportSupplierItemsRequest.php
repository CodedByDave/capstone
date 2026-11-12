<?php

namespace App\Http\Requests\SupplierPortal;

use App\Enums\AccountType;
use App\Services\SupplierItemImportService;
use Illuminate\Foundation\Http\FormRequest;

/** The supplies the supplier reviewed after importing a CSV or scanning a price list. */
class ImportSupplierItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::Supplier->value;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (is_array($items)) {
            $this->merge(['items' => array_map(fn ($item) => is_array($item) ? [
                ...$item,
                'name' => is_string($item['name'] ?? null) ? trim($item['name']) : ($item['name'] ?? null),
                'unit' => is_string($item['unit'] ?? null) ? trim($item['unit']) : ($item['unit'] ?? null),
            ] : $item, $items)]);
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:'.SupplierItemImportService::MAX_ROWS],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.stock_quantity' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'There are no supplies to add.',
            'items.max' => 'Add up to '.SupplierItemImportService::MAX_ROWS.' supplies at a time.',
            'items.*.name.required' => 'Enter a name.',
            'items.*.stock_quantity.required' => 'Enter the stock.',
            'items.*.stock_quantity.numeric' => 'Stock must be a number.',
            'items.*.stock_quantity.min' => 'Stock cannot be negative.',
            'items.*.unit_price.required' => 'Enter the price.',
            'items.*.unit_price.numeric' => 'Price must be a number.',
            'items.*.unit_price.min' => 'Price cannot be negative.',
        ];
    }

    /** @return list<array{name: string, unit: ?string, stock_quantity: mixed, unit_price: mixed}> */
    public function rows(): array
    {
        return array_values(array_map(fn (array $item) => [
            'name' => $item['name'],
            'unit' => $item['unit'] ?? null,
            'stock_quantity' => $item['stock_quantity'],
            'unit_price' => $item['unit_price'],
        ], $this->validated('items')));
    }
}
