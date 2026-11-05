<?php

namespace App\Http\Requests\Driver;

use App\Models\DeliveryRejection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(DeliveryRejection::REASONS))],
            // "Other" is not a reason on its own, so the driver has to say what happened.
            'note' => ['nullable', 'required_if:reason,other', 'string', 'min:5', 'max:500'],
            'proof_photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Choose why you cannot take this order.',
            'note.required_if' => 'Tell the shop what happened.',
            'note.min' => 'Describe the reason in a few words.',
            'proof_photo.required' => 'Take or upload a photo as proof of your reason.',
            'proof_photo.image' => 'The proof must be a photo.',
            'proof_photo.mimes' => 'The proof photo must be a JPG, PNG, or WebP image.',
            'proof_photo.max' => 'The proof photo must be 5 MB or smaller.',
        ];
    }
}
