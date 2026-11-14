<?php

namespace App\Http\Requests\User;

use App\Services\CustomerShopCatalogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Search, sort and filter input of the customer's "Shops" page. */
class CustomerShopsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('open')) {
            $this->merge(['open' => $this->boolean('open')]);
        }

        if (is_string($this->input('search'))) {
            $this->merge(['search' => trim($this->input('search'))]);
        }
    }

    public function rules(): array
    {
        return [
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(CustomerShopCatalogService::SORTS)],
            'open' => ['sometimes', 'boolean'],
        ];
    }

    /** The chosen ordering, or null to use the default (nearest when the location is known). */
    public function sortBy(): ?string
    {
        return $this->validated('sort') ?: null;
    }

    public function openOnly(): bool
    {
        return $this->boolean('open');
    }
}
