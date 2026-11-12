<?php

namespace App\Http\Requests\Shop\Inventory;

use App\Http\Requests\TableSortRequest;
use App\Models\PurchaseOrder;
use Illuminate\Validation\Rule;

class PurchaseOrderFilterRequest extends TableSortRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(PurchaseOrder::STATUSES)],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ];
    }

    /** @return array{search?: string, status?: string, per_page?: int} */
    public function filters(): array
    {
        return $this->safe()->only(['search', 'status', 'per_page']);
    }
}
