<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewBusinessAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['nullable', 'string', 'max:150'],
            'signer_name' => ['nullable', 'string', 'max:150'],
            'signer_role' => ['nullable', 'string', Rule::in(['Owner', 'Authorized Representative'])],
        ];
    }
}
