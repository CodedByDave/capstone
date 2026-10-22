<?php

namespace App\Http\Requests\Shop\Logistics;

use App\Http\Requests\Shop\Logistics\Concerns\ValidatesCourier;
use App\Services\LogisticsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryStatusRequest extends FormRequest
{
    use ValidatesCourier;

    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true || $this->user()?->isStaff() === true;
    }

    public function rules(): array
    {
        $shopId = app(LogisticsService::class)->shopIdFor($this->user());

        return [
            'status' => ['required', 'in:pending,assigned,picked_up,delivered,failed'],
            'rider_id' => ['nullable', Rule::exists('riders', 'id')->where('shop_id', $shopId)],
            'notes' => ['nullable', 'string', 'max:500'],
            ...$this->courierRules(['assigned', 'picked_up', 'delivered']),
        ];
    }
}
