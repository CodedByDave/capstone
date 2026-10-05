<?php

namespace App\Http\Requests\Shop\Logistics;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return [
            'shop_order_id' => ['nullable', 'exists:shop_orders,id'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'rider_id' => ['nullable', 'exists:riders,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
