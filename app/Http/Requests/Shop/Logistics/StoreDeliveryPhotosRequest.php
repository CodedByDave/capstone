<?php

namespace App\Http\Requests\Shop\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryPhotosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true || $this->user()?->isStaff() === true;
    }

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.*.image' => 'Each proof must be an image.',
            'photos.*.mimes' => 'Proof photos must be JPG, PNG, or WebP.',
            'photos.*.max' => 'Each proof photo must be 5 MB or smaller.',
        ];
    }
}
