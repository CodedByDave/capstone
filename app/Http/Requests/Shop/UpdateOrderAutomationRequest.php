<?php

namespace App\Http\Requests\Shop;

use App\Models\ShopOrderAutomationSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderAutomationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only the shop owner decides what the system may do on the shop's behalf.
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return [
            'start_on_weigh_in' => ['required', 'boolean'],
            'start_on_payment' => ['required', 'boolean'],
            'auto_complete' => ['required', 'boolean'],
            'auto_complete_grace_minutes' => ['required', 'integer', 'between:0,'.ShopOrderAutomationSetting::MAX_GRACE_MINUTES],
            'remind_overdue' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'auto_complete_grace_minutes.required' => 'Enter how many minutes to wait after the due time.',
            'auto_complete_grace_minutes.integer' => 'The wait must be a whole number of minutes.',
            'auto_complete_grace_minutes.between' => 'The wait must be between 0 and '.ShopOrderAutomationSetting::MAX_GRACE_MINUTES.' minutes (24 hours).',
        ];
    }
}
