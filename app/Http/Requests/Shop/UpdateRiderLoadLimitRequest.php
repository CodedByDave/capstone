<?php

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRiderLoadLimitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        // An empty field means no limit on that side.
        foreach (['rider_min_weight_kg', 'rider_max_weight_kg'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if (is_array($this->input('rider_vehicle_types'))) {
            $this->merge(['rider_vehicle_types' => $this->cleanVehicleTypes($this->input('rider_vehicle_types'))]);
        }

        if (is_string($this->input('rider_vehicle_type'))) {
            $this->merge(['rider_vehicle_type' => Str::squish($this->input('rider_vehicle_type'))]);
        }
    }

    public function rules(): array
    {
        return [
            'rider_min_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
            'rider_max_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
            'rider_vehicle_types' => ['required_with:rider_vehicle_type', 'array', 'max:10'],
            'rider_vehicle_types.*' => ['string', 'max:40'],
            'rider_vehicle_type' => [
                'required_with:rider_vehicle_types',
                'string',
                Rule::in((array) $this->input('rider_vehicle_types')),
            ],
        ];
    }

    /**
     * Motorcycle is always a choice and comes first. Repeats are dropped, ignoring case.
     *
     * @param  array<int|string, mixed>  $types
     * @return list<string>
     */
    private function cleanVehicleTypes(array $types): array
    {
        return collect([Shop::DEFAULT_RIDER_VEHICLE_TYPE, ...$types])
            ->filter(fn ($type) => is_string($type))
            ->map(fn (string $type) => Str::squish($type))
            ->filter()
            ->unique(fn (string $type) => mb_strtolower($type))
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'rider_min_weight_kg.numeric' => 'Enter the minimum weight in kilograms.',
            'rider_min_weight_kg.min' => 'The minimum weight must be at least 0.5 kg.',
            'rider_min_weight_kg.max' => 'The minimum weight cannot be more than 200 kg.',
            'rider_max_weight_kg.numeric' => 'Enter the maximum weight in kilograms.',
            'rider_max_weight_kg.min' => 'The maximum weight must be at least 0.5 kg.',
            'rider_max_weight_kg.max' => 'The maximum weight cannot be more than 200 kg.',
            'rider_vehicle_type.required_with' => 'Choose a vehicle type.',
            'rider_vehicle_type.in' => 'Choose a vehicle type from the list.',
            'rider_vehicle_types.max' => 'You can have up to 10 vehicle types.',
            'rider_vehicle_types.*.max' => 'A vehicle type cannot be longer than 40 characters.',
        ];
    }

    /** @return array<int, \Closure> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $min = $this->input('rider_min_weight_kg');
                $max = $this->input('rider_max_weight_kg');

                if (is_numeric($min) && is_numeric($max) && (float) $max < (float) $min) {
                    $validator->errors()->add('rider_max_weight_kg', 'The maximum weight cannot be less than the minimum weight.');
                }
            },
        ];
    }
}
