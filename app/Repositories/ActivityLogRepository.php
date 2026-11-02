<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogRepository
{
    use AppliesTableSort;

    /**
     * One page of a shop's activity logs. Filters: action, performed_by, module, date_from,
     * date_to and search (the subject's name).
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int>|null  $performerIds
     */
    public function paginateForShop(int $shopId, array $filters, ?array $performerIds, int $perPage, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = ActivityLog::query()
            ->where('shop_id', $shopId)
            ->when($performerIds !== null, fn ($q) => $q->whereIn('performed_by', $performerIds))
            ->with(['subject', 'performer:id,name'])
            ->when($filters['action'] ?? null, fn ($q) => $q->where('action', $filters['action']))
            ->when($filters['performed_by'] ?? null, fn ($q) => $q->where('performed_by', (int) $filters['performed_by']))
            ->when($filters['module'] ?? null, fn ($q) => $q->where('module', $filters['module']))
            ->when($filters['date_from'] ?? null, fn ($q) => $q->where(
                'created_at',
                '>=',
                \Carbon\Carbon::parse($filters['date_from'], 'Asia/Manila')->startOfDay()->utc()
            ))
            ->when($filters['date_to'] ?? null, fn ($q) => $q->where(
                'created_at',
                '<=',
                \Carbon\Carbon::parse($filters['date_to'], 'Asia/Manila')->endOfDay()->utc()
            ))
            ->when(
                $filters['search'] ?? null,
                fn ($q) => $q->whereHasMorph(
                    'subject',
                    '*',
                    fn ($sq, $type) => match (true) {
                        str_ends_with($type, 'Employee') => $sq->where(
                            fn ($s) => $s->where('first_name', 'like', "%{$filters['search']}%")
                                ->orWhere('last_name', 'like', "%{$filters['search']}%")
                        ),
                        default => $sq->where('name', 'like', "%{$filters['search']}%"),
                    }
                )
            );

        $sorted = $this->applyTableSort($query, $sort, [
            'created_at' => 'created_at',
            'module' => 'module',
            'action' => 'action',
            'performer' => fn ($q, $dir) => $q->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'activity_logs.performed_by'),
                $dir,
            ),
        ]);

        if (! $sorted) {
            $query->latest();
        }

        return $query->paginate($perPage)->withQueryString();
    }

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

    /**
     * Most recent log entries performed by one user within a shop.
     *
     * @return Collection<int, ActivityLog>
     */
    public function recentByPerformer(int $shopId, int $userId, int $limit = 10): Collection
    {
        return ActivityLog::query()
            ->where('shop_id', $shopId)
            ->where('performed_by', $userId)
            ->with('subject')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
