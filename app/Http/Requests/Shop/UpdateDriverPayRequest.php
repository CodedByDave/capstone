<?php

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverPayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('driver_daily_rate') && $this->input('driver_daily_rate') === '') {
            $this->merge(['driver_daily_rate' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'driver_pay_mode' => ['required', Rule::in(Shop::DRIVER_PAY_MODES)],
            'driver_daily_rate' => ['nullable', 'required_if:driver_pay_mode,'.Shop::DRIVER_PAY_DAILY_RATE, 'numeric', 'min:1', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'driver_pay_mode.in' => 'Choose how driver earnings are shown.',
            'driver_daily_rate.required_if' => 'Enter the daily rate for drivers.',
        ];
    }
}
