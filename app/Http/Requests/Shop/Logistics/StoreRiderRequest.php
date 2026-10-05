<?php

namespace App\Http\Requests\Shop\Logistics;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreRiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
        ];
    }
}
