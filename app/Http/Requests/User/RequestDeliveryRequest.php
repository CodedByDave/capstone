<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RequestDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('delivery_address'))) {
            $this->merge(['delivery_address' => trim($this->input('delivery_address'))]);
        }
    }

    public function rules(): array
    {
        return [
            'delivery_address' => ['required', 'string', 'max:500'],
            'destination_lat' => ['required', 'numeric', 'between:-90,90'],
            'destination_lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_address.required' => 'Enter your delivery address.',
            'destination_lat.required' => 'Drop a pin on the map to set your delivery location.',
            'destination_lng.required' => 'Drop a pin on the map to set your delivery location.',
            'destination_lat.between' => 'The pinned location is not valid.',
            'destination_lng.between' => 'The pinned location is not valid.',
        ];
    }
}
