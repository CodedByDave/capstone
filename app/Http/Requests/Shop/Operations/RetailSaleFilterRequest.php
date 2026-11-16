<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Search, filter, sort and paging of the Retail Products list. */
class RetailSaleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may open the list is decided by the route (owner, or staff with Operations view).
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['per_page' => min(max($this->integer('per_page', 15), 5), 100)]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(ShopOrder::STATUSES)],
            'source' => ['nullable', Rule::in(['shop', 'online'])],
            'sort_by' => ['nullable', Rule::in(['order_number', 'customer', 'status', 'payment_status', 'total', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'type' => 'products',
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
            'source' => $validated['source'] ?? null,
            'sort_by' => $validated['sort_by'] ?? 'created_at',
            'sort_direction' => $validated['sort_direction'] ?? 'desc',
            'per_page' => (int) $validated['per_page'],
        ];
    }
}
