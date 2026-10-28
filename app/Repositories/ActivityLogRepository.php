<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Support\Collection;

class ActivityLogRepository
{
    /**
     * Number of log entries per action for a shop, optionally limited to some performers.
     *
     * @param  array<int>|null  $performerIds
     * @return Collection<string, int>
     */
    public function countsByAction(int $shopId, ?array $performerIds = null): Collection
    {
        return ActivityLog::query()
            ->where('shop_id', $shopId)
            ->when($performerIds !== null, fn ($q) => $q->whereIn('performed_by', $performerIds))
            ->selectRaw('action, count(*) as total')
            ->groupBy('action')
            ->pluck('total', 'action');
    }
}
