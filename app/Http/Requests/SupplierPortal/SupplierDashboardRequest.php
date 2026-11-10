<?php

namespace App\Http\Requests\SupplierPortal;

use App\Http\Requests\TableSortRequest;
use Illuminate\Validation\Rule;

/** Search, stock filter, sort and paging of the supplier's catalog table. */
class SupplierDashboardRequest extends TableSortRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'search' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', Rule::in(['', 'in_stock', 'out_of_stock'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['search'] ?? '',
            'stock' => $validated['stock'] ?? '',
            'per_page' => (int) ($validated['per_page'] ?? 10),
        ];
    }
}
