<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopOrderFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may open the list is decided by the route (owner, or staff with Operations view).
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => min(max($this->integer('per_page', 15), 5), 100),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(ShopOrder::STATUSES)],
            'payment_status' => ['nullable', Rule::in(ShopOrder::PAYMENT_STATUSES)],
            'archived' => ['nullable', 'boolean'],
            'new_only' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', Rule::in([
                'order_number', 'customer', 'service', 'status', 'payment_status', 'total', 'created_at',
            ])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * The validated filters with defaults filled in: newest first, 15 per page, active orders.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
            'payment_status' => $validated['payment_status'] ?? null,
            'archived' => (bool) ($validated['archived'] ?? false),
            'new_only' => (bool) ($validated['new_only'] ?? false),
            'sort_by' => $validated['sort_by'] ?? 'created_at',
            'sort_direction' => $validated['sort_direction'] ?? 'desc',
            'per_page' => (int) $validated['per_page'],
        ];
    }
}
