<?php

namespace App\Http\Requests\Shop\Logistics\Concerns;

use App\Models\Delivery;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Validation\Rule;

/**
 * Rules for handing a delivery to an outside courier such as Lalamove. The shop books
 * the run in the courier's own app and records the booking here.
 */
trait ValidatesCourier
{
    /** @param  list<string>  $referenceRequiredFor delivery statuses that need a booking reference */
    protected function courierRules(array $referenceRequiredFor = []): array
    {
        $isLalamove = fn () => $this->input('courier') === Delivery::COURIER_LALAMOVE;

        return [
            'courier' => ['nullable', Rule::in(Delivery::COURIERS)],
            'courier_reference' => [
                Rule::requiredIf(fn () => $isLalamove() && in_array($this->input('status'), $referenceRequiredFor, true)),
                'nullable', 'string', 'max:100',
            ],
            'courier_tracking_url' => ['nullable', 'url:https', 'max:500'],
            'courier_driver_name' => ['nullable', 'string', 'max:100'],
            'courier_driver_phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            'courier_vehicle_plate' => ['nullable', 'string', 'max:20'],
            'courier_cost' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ];
    }
}
