<?php

namespace App\Services\Sms;

use App\Exceptions\SmsGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * The platform sender: TextBee (https://textbee.dev) sends texts for every shop from one Android
 * phone the platform runs, so shop owners have nothing to install or set up.
 */
class TextBeeClient implements PlatformSmsSender
{
    public const PROVIDER = 'textbee';

    public function provider(): string
    {
        return self::PROVIDER;
    }

    public function isConfigured(): bool
    {
        return filled(config('services.textbee.key'));
    }

    /** @return string|null TextBee's batch id, used to look the message up in its dashboard. */
    public function send(string $phone, string $text): ?string
    {
        if (! $this->isConfigured()) {
            throw new SmsGatewayException('Texting is not set up on this server.');
        }

        $body = ['recipients' => [$phone], 'message' => $text];
        if (filled(config('services.textbee.device_id'))) {
            $body['deviceId'] = config('services.textbee.device_id');
        }

        try {
            $response = Http::withHeaders(['x-api-key' => config('services.textbee.key')])
                ->acceptJson()
                ->timeout(config('services.textbee.timeout', 15))
                ->post(rtrim((string) config('services.textbee.base_url'), '/').'/gateway/send-sms', $body);
        } catch (ConnectionException) {
            throw new SmsGatewayException('Could not reach the texting service. Try again in a moment.');
        }

        $reason = $response->json('message') ?? $response->json('error');

        throw_if($response->status() === 401, SmsGatewayException::class, 'The texting service rejected the API key.');
        throw_if($response->status() === 429, SmsGatewayException::class, 'The daily or monthly texting limit is used up.');
        throw_if($response->status() === 400, SmsGatewayException::class,
            'No phone is ready to send the text. Check that the texting phone is on and online.');
        throw_if($response->failed(), SmsGatewayException::class,
            'The texting service could not send the text'.(is_string($reason) ? ': '.$reason : '.'));

        if ($response->json('data.success') === false) {
            throw new SmsGatewayException('The texting service did not accept the text'
                .(is_string($response->json('data.message')) ? ': '.$response->json('data.message') : '.'));
        }

        return $response->json('data.smsBatchId');
    }
}
