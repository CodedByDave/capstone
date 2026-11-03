<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class ScanKycDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Choose a document to scan.',
            'document.mimes' => 'Document must be a PDF, JPG, or PNG.',
            'document.max' => 'Document must not exceed 5MB.',
        ];
    }
}
