<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Choose a photo to upload.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'Use a JPG, PNG or WebP image.',
            'photo.max' => 'The photo may not be larger than 4 MB.',
        ];
    }
}
