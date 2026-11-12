<?php

namespace App\Services\Sms;

use App\Exceptions\SmsGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * PhilSMS (https://philsms.com), a Philippine SMS API: texts go straight to the networks, so no
 * phone has to stay on. Each text uses prepaid credit from the PhilSMS account.
 */
class PhilSmsClient implements PlatformSmsSender
{
    public function provider(): string
    {
        return 'philsms';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.philsms.token'));
    }

    public function send(string $phone, string $text): ?string
    {
        if (! $this->isConfigured()) {
            throw new SmsGatewayException('Texting is not set up on this server.');
        }

        try {
            $response = Http::withToken((string) config('services.philsms.token'))
                ->acceptJson()
                ->timeout(config('services.philsms.timeout', 15))
                ->post(rtrim((string) config('services.philsms.base_url'), '/').'/sms/send', [
                    // PhilSMS takes numbers as 639XXXXXXXXX, without the plus sign.
                    'recipient' => ltrim($phone, '+'),
                    'sender_id' => config('services.philsms.sender_id'),
                    'type' => 'plain',
                    'message' => $text,
                ]);
        } catch (ConnectionException) {
            throw new SmsGatewayException('Could not reach PhilSMS. Try again in a moment.');
        }

        $reason = $response->json('message');

        if ($response->status() === 401 || $response->status() === 403) {
            throw new SmsGatewayException('PhilSMS rejected the API token.');
        }

        // PhilSMS reports problems such as no credit left with "status": "error", sometimes on a 200.
        if ($response->failed() || $response->json('status') !== 'success') {
            throw new SmsGatewayException('PhilSMS could not send the text'
                .(is_string($reason) && $reason !== '' ? ': '.$reason : '. Check the account credit.'));
        }

        $data = $response->json('data');

        return is_array($data) ? ($data['uid'] ?? null) : null;
    }
}
