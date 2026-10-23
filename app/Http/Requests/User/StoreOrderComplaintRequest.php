<?php

namespace App\Http\Requests\User;

use App\Models\OrderComplaint;
use App\Services\OrderComplaintService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreOrderComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    protected function prepareForValidation(): void
    {
        $description = $this->input('description');

        $this->merge([
            'description' => is_string($description) ? trim($description) : $description,
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(OrderComplaint::TYPES))],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:'.OrderComplaintService::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Choose what went wrong.',
            'description.required' => 'Describe the problem so the shop can help.',
            'description.min' => 'Please describe the problem in at least 10 characters.',
            'description.max' => 'Keep the description to 1000 characters or fewer.',
            'photos.max' => 'You can attach up to '.OrderComplaintService::MAX_PHOTOS.' photos.',
            'photos.*.image' => 'Photos must be JPG, PNG or WebP images.',
            'photos.*.mimes' => 'Photos must be JPG, PNG or WebP images.',
            'photos.*.max' => 'Each photo must be 5 MB or smaller.',
        ];
    }

    /** @return list<UploadedFile> */
    public function photoFiles(): array
    {
        return array_values($this->file('photos') ?? []);
    }
}
