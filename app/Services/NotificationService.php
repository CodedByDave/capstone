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
                'created_at_iso' => $notification->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    /** Notification types that should pop up on screen while a shop user is working. */
    public const POPUP_TYPES = ['new_online_order'];

    /** @return list<array{id: string, type: ?string, title: string, body: string, url: ?string}> */
    public function popupsFor(User $user): array
    {
        return $this->notifications->unreadOfTypes($user, self::POPUP_TYPES)
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? null,
                'title' => $notification->data['title'] ?? '',
                'body' => $notification->data['body'] ?? '',
                'url' => $notification->data['url'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->unreadCount($user);
    }

    public function markRead(User $user, string $id): void
    {
        $this->notifications->markRead($user, $id);
    }

    public function markAllRead(User $user): void
    {
        $this->notifications->markAllRead($user);
    }
}
