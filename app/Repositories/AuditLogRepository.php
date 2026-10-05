<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AuditLogRepository
{
    public function getPaginated(
        array $filters,
        int $perPage,
        bool $archived = false,
    ): LengthAwarePaginator {
        return $this->filteredQuery($filters, $archived)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getForExport(array $filters): Collection
    {
        return $this->filteredQuery($filters)->get();
    }

    public function find(string $category, int $id): object
    {
        $this->modelFor($category)->findOrFail($id);

        return DB::query()
            ->fromSub($this->unionQuery(), 'audit_entries')
            ->where('category', $category)
            ->where('record_id', $id)
            ->firstOrFail();
    }

    public function getStats(): array
    {
        $authentication = LoginLog::query()->count();
        $activities = ActivityLog::query()->count();

        return [
            'total' => $authentication + $activities,
            'authentication' => $authentication,
            'activities' => $activities,
            'failed' => LoginLog::query()->where('status', 'failed')->count(),
            'archived' => LoginLog::onlyTrashed()->count() + ActivityLog::onlyTrashed()->count(),
        ];
    }

    public function getModules(): Collection
    {
        return ActivityLog::query()
            ->distinct()
            ->orderBy('module')
            ->pluck('module');
    }

    public function usersByEmail(Collection $emails): Collection
    {
        return User::withTrashed()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy(fn (User $user) => strtolower($user->email));
    }

    public function shopsByName(Collection $names): Collection
    {
        return Shop::withTrashed()
            ->whereIn('shop_name', $names)
            ->get()
            ->keyBy('shop_name');
    }

    public function existingAuthenticationKeys(Collection $emails, Collection $dates): Collection
    {
        return LoginLog::withTrashed()
            ->whereIn('email', $emails)
            ->whereIn('logged_at', $dates)
            ->get(['email', 'status', 'logged_at'])
            ->mapWithKeys(fn (LoginLog $log) => [
                strtolower($log->email).'|'.$log->status.'|'.$log->logged_at->format('Y-m-d H:i:s') => true,
            ]);
    }

    public function existingActivityKeys(Collection $dates): Collection
    {
        return ActivityLog::withTrashed()
            ->whereIn('created_at', $dates)
            ->get(['module', 'action', 'performed_by', 'created_at'])
            ->mapWithKeys(fn (ActivityLog $log) => [
                $log->module.'|'.$log->action.'|'.($log->performed_by ?? 'null').'|'.$log->created_at->format('Y-m-d H:i:s') => true,
            ]);
    }

    public function createAuthentication(array $attributes): LoginLog
    {
        return LoginLog::create($attributes);
    }

    public function createActivity(array $attributes, string $occurredAt): ActivityLog
    {
        $activity = new ActivityLog($attributes);
        $activity->created_at = $occurredAt;
        $activity->updated_at = $occurredAt;
        $activity->save();

        return $activity;
    }

    public function archive(string $category, int $id): void
    {
        $this->modelFor($category)->findOrFail($id)->delete();
    }

    public function bulkArchive(array $entries): void
    {
        $this->groupEntries($entries)->each(function (Collection $items, string $category) {
            $this->modelFor($category)
                ->whereIn('id', $items->pluck('id'))
                ->delete();
        });
    }

    public function restore(string $category, int $id): void
    {
        $this->modelFor($category)->onlyTrashed()->findOrFail($id)->restore();
    }

    public function bulkRestore(array $entries): void
    {
        $this->groupEntries($entries)->each(function (Collection $items, string $category) {
            $this->modelFor($category)
                ->onlyTrashed()
                ->whereIn('id', $items->pluck('id'))
                ->restore();
        });
    }

    public function forceDelete(string $category, int $id): void
    {
        $this->modelFor($category)->onlyTrashed()->findOrFail($id)->forceDelete();
    }

    private function filteredQuery(array $filters, bool $archived = false): Builder
    {
        $query = DB::query()->fromSub($this->unionQuery($archived), 'audit_entries');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }

        foreach (['category', 'role', 'module', 'event'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (! empty($filters['date'])) {
            $query->whereDate('occurred_at', $filters['date']);
        }

        return $query->orderBy(
            $filters['sort_by'] ?? 'occurred_at',
            ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc',
        );
    }

    private function unionQuery(bool $archived = false): Builder
    {
        $authentication = DB::table('login_logs')
            ->leftJoin('users', 'users.id', '=', 'login_logs.user_id')
            ->when(
                $archived,
                fn (Builder $query) => $query->whereNotNull('login_logs.deleted_at'),
                fn (Builder $query) => $query->whereNull('login_logs.deleted_at'),
            )
            ->select([
                'login_logs.id as record_id',
                DB::raw("'authentication' as category"),
                'login_logs.user_id',
                'users.public_id as user_public_id',
                DB::raw('COALESCE(login_logs.name, users.name) as name'),
                DB::raw('COALESCE(login_logs.email, users.email) as email'),
                DB::raw('COALESCE(login_logs.role, users.role) as role'),
                DB::raw("'Authentication' as module"),
                'login_logs.status as event',
                'login_logs.failure_reason as details',
                'login_logs.ip_address',
                'login_logs.user_agent',
                DB::raw('NULL as shop_name'),
                'login_logs.logged_at as occurred_at',
                'login_logs.deleted_at as archived_at',
                DB::raw('1 as archivable'),
            ]);

        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'users.id', '=', 'activity_logs.performed_by')
            ->leftJoin('shops', 'shops.id', '=', 'activity_logs.shop_id')
            ->when(
                $archived,
                fn (Builder $query) => $query->whereNotNull('activity_logs.deleted_at'),
                fn (Builder $query) => $query->whereNull('activity_logs.deleted_at'),
            )
            ->select([
                'activity_logs.id as record_id',
                DB::raw("'activity' as category"),
                'activity_logs.performed_by as user_id',
                'users.public_id as user_public_id',
                'users.name',
                'users.email',
                'users.role',
                'activity_logs.module',
                'activity_logs.action as event',
                'activity_logs.changes as details',
                DB::raw('NULL as ip_address'),
                DB::raw('NULL as user_agent'),
                'shops.shop_name',
                'activity_logs.created_at as occurred_at',
                'activity_logs.deleted_at as archived_at',
                DB::raw('1 as archivable'),
            ]);

        return $authentication->unionAll($activities);
    }

    private function groupEntries(array $entries): Collection
    {
        return collect($entries)->groupBy('category');
    }

    private function modelFor(string $category): Model
    {
        return match ($category) {
            'authentication' => new LoginLog,
            'activity' => new ActivityLog,
            default => throw new InvalidArgumentException("Unknown audit log category [{$category}]."),
        };
    }
}
