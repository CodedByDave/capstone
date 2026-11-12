<?php

namespace App\Services\Sms;

/**
 * The service that texts customers for every shop. Which one is used is set once on the server
 * (SMS_DRIVER in .env), so switching providers needs no code change.
 */
interface PlatformSmsSender
{
    /** Short name stored with each text, e.g. "textbee" or "philsms". */
    public function provider(): string;

    /** Whether the server has the credentials this sender needs. */
    public function isConfigured(): bool;

    /**
     * @param  string  $phone  Philippine mobile number in +63 form.
     * @return string|null The provider's id for the message, when it returns one.
     *
     * @throws \App\Exceptions\SmsGatewayException
     */
    public function send(string $phone, string $text): ?string;
}
