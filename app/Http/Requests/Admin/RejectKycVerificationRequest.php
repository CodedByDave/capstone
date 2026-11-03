<?php

namespace App\Http\Requests\Admin;

use App\Services\KycReviewService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectKycVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('rejection_reason'))) {
            $this->merge(['rejection_reason' => trim($this->input('rejection_reason'))]);
        }
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
            'flags' => ['nullable', 'array'],
            'flags.*' => ['string', Rule::in(KycReviewService::FLAGGABLE)],
        ];
    }

    public function messages(): array
    {
        return ['rejection_reason.required' => 'Please provide a reason for rejection.'];
    }
}
