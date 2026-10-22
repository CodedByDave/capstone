<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    public function rules(): array
    {
        return [
            'destination_lat' => ['required', 'numeric', 'between:-90,90'],
            'destination_lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_lat.required' => 'Drop a pin on the map to get a delivery quote.',
            'destination_lng.required' => 'Drop a pin on the map to get a delivery quote.',
        ];
    }
}
