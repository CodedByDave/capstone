<?php

namespace App\Http\Requests\User;

use App\Models\ShopOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Filters of the customer's "My Orders" list. */
class IndexCustomerOrdersRequest extends FormRequest
{
    /** Tabs: a status, or "completed" for everything finished (completed and picked up). */
    public const STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['status', 'payment'] as $field) {
            if ($this->input($field) === 'all' || $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if (is_string($this->input('search'))) {
            $this->merge(['search' => trim($this->input('search'))]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'payment' => ['nullable', Rule::in(ShopOrder::PAYMENT_STATUSES)],
            'search' => ['nullable', 'string', 'max:60'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array{status: ?string, payment: ?string, search: ?string} */
    public function filters(): array
    {
        return [
            'status' => $this->validated('status'),
            'payment' => $this->validated('payment'),
            'search' => $this->validated('search') ?: null,
        ];
    }
}
