<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notifications,
    ) {}

    public function listFor(User $user): array
    {
        return $this->notifications->latestFor($user)
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at->diffForHumans(),
            ])
            ->values()
            ->all();
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->unreadCount($user);
    }

    public function markAllRead(User $user): void
    {
        $this->notifications->markAllRead($user);
    }
}
