<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:picked_up,delivered,failed'],
            'notes' => ['nullable', 'string', 'max:500'],
            // Cash the rider took at the door when handing the laundry over (0 when nothing was paid).
            'collected_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'decimal:0,2'],
            // Photo of the cash taken at the door; required by the service whenever cash was collected.
            'proof_photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'collected_amount.decimal' => 'The amount can have at most 2 decimal places.',
            'proof_photo.image' => 'The proof must be a photo.',
            'proof_photo.mimes' => 'The proof photo must be a JPG, PNG, or WebP image.',
            'proof_photo.max' => 'The proof photo must be 5 MB or smaller.',
        ];
    }
}
