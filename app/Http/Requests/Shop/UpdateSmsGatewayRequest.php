<?php

namespace App\Http\Requests\Shop;

use App\Services\Sms\SmsGatewayClient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Whether the shop texts customers, and its optional own-phone sender. Only the owner can change it. */
class UpdateSmsGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled' => $this->boolean('enabled'),
            // The own-phone fields sit under Advanced; most owners only send the On/Off switch.
            'mode' => $this->input('mode') ?: 'cloud',
            'notify_ready' => $this->boolean('notify_ready'),
            'url' => is_string($this->url) ? trim($this->url) : $this->url,
            'username' => is_string($this->username) ? trim($this->username) : $this->username,
        ]);
    }

    public function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'mode' => ['required', Rule::in([SmsGatewayClient::MODE_CLOUD, SmsGatewayClient::MODE_LOCAL])],
            'url' => ['exclude_unless:mode,local', 'required', 'url:http,https', 'max:255'],
            'username' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:255'],
            'notify_ready' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'Enter the address shown in the app, like http://192.168.1.20:8080.',
            'url.url' => 'Enter the address shown in the app, like http://192.168.1.20:8080.',
        ];
    }
}
