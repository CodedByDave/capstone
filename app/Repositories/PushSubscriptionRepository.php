<?php

namespace App\Repositories;

use App\Models\User;

/** Stores the browser push subscriptions a user has registered (one per device). */
class PushSubscriptionRepository
{
    public function save(User $user, string $endpoint, ?string $publicKey, ?string $authToken, ?string $contentEncoding): void
    {
        $user->updatePushSubscription($endpoint, $publicKey, $authToken, $contentEncoding);
    }

    public function delete(User $user, string $endpoint): void
    {
        $user->deletePushSubscription($endpoint);
    }
}
