<?php

namespace App\Http\Requests;

use App\DTOs\TableSort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sort input shared by the paginated data tables. Which columns are really sortable is decided by
 * each repository's whitelist, so an unknown column simply falls back to the table's default order.
 */
class TableSortRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may open the page is decided by the route.
        return true;
    }

    public function rules(): array
    {
        return [
            'sort_by' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_]+$/'],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function sort(): TableSort
    {
        $validated = $this->validated();

        return new TableSort(
            $validated['sort_by'] ?? null,
            ($validated['sort_direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
        );
    }
}
