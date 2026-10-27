<?php

namespace App\Http\Requests\Shop\Operations;

use Illuminate\Foundation\Http\FormRequest;

class ImportShopServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may create services is decided by the route (owner, or staff with Operations create).
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Please choose a CSV file to import.',
            'csv_file.mimes' => 'The file must be a .csv file.',
            'csv_file.max' => 'The CSV file must be 2 MB or smaller.',
        ];
    }
}
