<?php

namespace App\Http\Requests\User;

use App\Models\CustomerAddress;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['recipient_name', 'address'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'label' => ['required', Rule::in(CustomerAddress::LABELS)],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            // The exact pinned spot, so the pickup fee and rider tracking work.
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Enter the name of the person receiving the rider.',
            'address.required' => 'Enter the complete address.',
            'latitude.required' => 'Pin your exact location on the map.',
            'longitude.required' => 'Pin your exact location on the map.',
            'address.min' => 'The address is too short. Include the street and barangay.',
        ];
    }
}
