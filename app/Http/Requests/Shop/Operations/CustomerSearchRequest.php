<?php

namespace App\Http\Requests\Shop\Operations;

use Illuminate\Foundation\Http\FormRequest;

/** The text typed into a customer name or number field on the order form. */
class CustomerSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may search is decided by the route (owner, or staff with Operations view).
        return true;
    }

    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:100']];
    }

    public function term(): string
    {
        return (string) ($this->validated()['q'] ?? '');
    }
}
