<?php

namespace App\Services\Sms;

use App\Exceptions\SmsGatewayException;
use App\Models\Shop;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Sends a text through SMS Gateway for Android (https://docs.sms-gate.app): the shop's own Android
 * phone sends it from its SIM. Cloud mode goes through api.sms-gate.app and works from a hosted
 * server; local mode calls the phone directly and only works when the server is on the same Wi-Fi.
 */
class SmsGatewayClient
{
    public const MODE_CLOUD = 'cloud';

    public const MODE_LOCAL = 'local';

    public const CLOUD_URL = 'https://api.sms-gate.app/3rdparty/v1';

    /** @return string|null The gateway's id for the message, when it returns one. */
    public function send(Shop $shop, string $phone, string $text): ?string
    {
        if (blank($shop->sms_gateway_username) || blank($shop->sms_gateway_password)) {
            throw new SmsGatewayException('The SMS gateway username and password are not set.');
        }

        try {
            $response = Http::withBasicAuth($shop->sms_gateway_username, $shop->sms_gateway_password)
                ->acceptJson()
                ->timeout(15)
                ->post($this->sendUrl($shop), [
                    'textMessage' => ['text' => $text],
                    'phoneNumbers' => [$phone],
                ]);
        } catch (ConnectionException) {
            throw new SmsGatewayException($shop->sms_gateway_mode === self::MODE_LOCAL
                ? 'Could not reach the phone. Check that it is on, on the same Wi-Fi, and the app\'s local server is started.'
                : 'Could not reach the SMS gateway. Check the internet connection and try again.');
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new SmsGatewayException('The SMS gateway rejected the username or password.');
        }

        if ($response->failed()) {
            $reason = $response->json('message') ?? $response->json('error');

            throw new SmsGatewayException('The SMS gateway could not send the text'.($reason ? ': '.$reason : '.'));
        }

        return $response->json('id');
    }

    private function sendUrl(Shop $shop): string
    {
        if ($shop->sms_gateway_mode === self::MODE_LOCAL) {
            return rtrim((string) $shop->sms_gateway_url, '/').'/message';
        }

        return self::CLOUD_URL.'/messages';
    }
}
