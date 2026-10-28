<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRepository
{
    public function latestFor(User $user, int $limit = 50): Collection
    {
        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markRead(User $user, string $id): void
    {
        $user->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function markAllRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
