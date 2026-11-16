<?php

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class UpdateRiderLoadLimitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('rider_vehicle_types'))) {
            $this->merge(['rider_vehicle_types' => $this->cleanVehicleTypes($this->input('rider_vehicle_types'))]);
        }

        if (is_array($this->input('rider_vehicle_limits'))) {
            $this->merge(['rider_vehicle_limits' => $this->alignVehicleLimits(
                $this->input('rider_vehicle_limits'),
                (array) $this->input('rider_vehicle_types', [Shop::DEFAULT_RIDER_VEHICLE_TYPE]),
            )]);
        }
    }

    public function rules(): array
    {
        return [
            'rider_vehicle_types' => ['required', 'array', 'max:10'],
            'rider_vehicle_types.*' => ['string', 'max:40'],
            'rider_vehicle_limits' => ['nullable', 'array', 'max:10'],
            'rider_vehicle_limits.*.type' => ['required', 'string', 'max:40'],
            'rider_vehicle_limits.*.min' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
            'rider_vehicle_limits.*.max' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
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

    /**
     * One capacity entry per vehicle type, in the order of the cleaned type list, so error keys
     * such as rider_vehicle_limits.1.max point at the same row the owner sees. Types are matched
     * ignoring case; a type without an entry has no limit.
     *
     * @param  array<int|string, mixed>  $limits
     * @param  array<int|string, mixed>  $types
     * @return list<array{type: string, min: mixed, max: mixed}>
     */
    private function alignVehicleLimits(array $limits, array $types): array
    {
        $byType = collect($limits)
            ->filter(fn ($entry) => is_array($entry) && is_string($entry['type'] ?? null))
            ->keyBy(fn (array $entry) => mb_strtolower(Str::squish($entry['type'])));

        return collect($this->cleanVehicleTypes($types))
            ->map(function (string $type) use ($byType) {
                $entry = $byType->get(mb_strtolower($type), []);

                return [
                    'type' => $type,
                    'min' => ($entry['min'] ?? null) === '' ? null : ($entry['min'] ?? null),
                    'max' => ($entry['max'] ?? null) === '' ? null : ($entry['max'] ?? null),
                ];
            })
            ->all();
    }

    public function messages(): array
    {
        return [
            'rider_vehicle_types.max' => 'You can have up to 10 vehicle types.',
            'rider_vehicle_types.*.max' => 'A vehicle type cannot be longer than 40 characters.',
            'rider_vehicle_limits.*.min.numeric' => 'Enter the minimum weight in kilograms.',
            'rider_vehicle_limits.*.min.min' => 'The minimum weight must be at least 0.5 kg.',
            'rider_vehicle_limits.*.min.max' => 'The minimum weight cannot be more than 200 kg.',
            'rider_vehicle_limits.*.max.numeric' => 'Enter the maximum weight in kilograms.',
            'rider_vehicle_limits.*.max.min' => 'The maximum weight must be at least 0.5 kg.',
            'rider_vehicle_limits.*.max.max' => 'The maximum weight cannot be more than 200 kg.',
        ];
    }

    /** @return array<int, \Closure> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('rider_vehicle_limits') as $i => $entry) {
                    $min = $entry['min'] ?? null;
                    $max = $entry['max'] ?? null;

                    if (is_numeric($min) && is_numeric($max) && (float) $max < (float) $min) {
                        $validator->errors()->add("rider_vehicle_limits.$i.max", 'The maximum weight cannot be less than the minimum weight.');
                    }
                }
            },
        ];
    }
}
