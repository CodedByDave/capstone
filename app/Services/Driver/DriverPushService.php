<?php

namespace App\Services\Driver;

use App\Models\User;
use App\Repositories\PushSubscriptionRepository;

/** Lets a driver's phone receive job alerts as system notifications while the app is closed. */
class DriverPushService
{
    public function __construct(private readonly PushSubscriptionRepository $subscriptions) {}

    /** The public VAPID key the browser needs to subscribe, or null when push is not configured. */
    public function publicKey(): ?string
    {
        return config('webpush.vapid.public_key') ?: null;
    }

    /** @param array{endpoint: string, keys?: array{p256dh?: ?string, auth?: ?string}, contentEncoding?: ?string} $subscription */
    public function subscribe(User $user, array $subscription): void
    {
        $this->subscriptions->save(
            $user,
            $subscription['endpoint'],
            $subscription['keys']['p256dh'] ?? null,
            $subscription['keys']['auth'] ?? null,
            $subscription['contentEncoding'] ?? null,
        );
    }

    public function unsubscribe(User $user, string $endpoint): void
    {
        $this->subscriptions->delete($user, $endpoint);
    }
}
