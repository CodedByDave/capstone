<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may change an order is decided by the route (owner, or staff with Operations edit).
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(ShopOrder::STATUSES)],
        ];
    }
}
