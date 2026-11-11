<?php

namespace App\Http\Requests\Admin;

use App\Services\KycDocumentVault;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Opening a KYC document in the Super Admin's viewer. The path must point inside the KYC folders,
 * so the viewer can never be used to read any other private file.
 */
class ViewKycDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'path' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! app(KycDocumentVault::class)->isKycPath($value)) {
                        $fail('That document does not exist.');
                    }
                },
            ],
        ];
    }

    /** A bad or foreign path gets the same not-found as a missing document, so nothing can be probed. */
    protected function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException;
    }

    public function documentPath(): string
    {
        return (string) $this->validated('path');
    }
}
