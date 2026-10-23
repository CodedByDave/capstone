<?php

namespace App\Http\Requests\Shop;

use App\Models\OrderComplaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may open the page is decided by the route (owner, or staff with Operations view).
        return $this->user()?->isOwner() === true || $this->user()?->isStaff() === true;
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
            'status' => ['nullable', Rule::in(OrderComplaint::STATUSES)],
            'type' => ['nullable', Rule::in(array_keys(OrderComplaint::TYPES))],
            'sort_by' => ['nullable', Rule::in(['order_number', 'customer', 'type', 'status', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return $this->validated() + ['sort_by' => 'created_at', 'sort_direction' => 'desc'];
    }
}
