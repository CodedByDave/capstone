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

    /** @param  list<string>  $types  values of the notification's data `type` */
    public function unreadOfTypes(User $user, array $types, int $limit = 5): Collection
    {
        return $user->unreadNotifications()
            ->whereIn('data->type', $types)
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
