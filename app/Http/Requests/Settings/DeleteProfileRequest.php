<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class DeleteProfileRequest extends FormRequest
{
    /** A supplier login belongs to the shop that created it, so only the shop can archive it. */
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isSupplier();
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
