<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
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
    }

    public function rules(): array
    {
        return [
            'rider_min_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
            'rider_max_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
        ];
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
