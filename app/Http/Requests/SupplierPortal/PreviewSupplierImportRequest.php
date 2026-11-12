<?php

namespace App\Http\Requests\SupplierPortal;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;

/** A CSV file or a price-list photo to read into supplies for the supplier to review. */
class PreviewSupplierImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::Supplier->value;
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required_without:photo', 'prohibits:photo', 'file', 'mimes:csv,txt', 'max:1024'],
            'photo' => ['required_without:csv_file', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required_without' => 'Choose a CSV file or a photo of your price list.',
            'csv_file.mimes' => 'The file must be a CSV (.csv).',
            'csv_file.max' => 'The CSV file must be 1 MB or smaller.',
            'photo.required_without' => 'Choose a CSV file or a photo of your price list.',
            'photo.mimes' => 'Upload the price list as a JPG, PNG or WebP photo.',
            'photo.max' => 'The photo must be 5 MB or smaller.',
        ];
    }
}
