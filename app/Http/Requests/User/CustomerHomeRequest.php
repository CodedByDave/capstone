<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/** Query input of the customer home: where the customer is. */
class CustomerHomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    public function rules(): array
    {
        return [
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
