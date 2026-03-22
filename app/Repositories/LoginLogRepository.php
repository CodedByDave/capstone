<?php

namespace App\Repositories;

use App\Models\LoginLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LoginLogRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new LoginLog());
    }

    // ── Active logs ───────────────────────────────────────────────────────────

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query()
            ->whereNull('deleted_at')
            ->latest('logged_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email',      'like', "%{$filters['search']}%")
                    ->orWhere('name',      'like', "%{$filters['search']}%")
                    ->orWhere('ip_address', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('logged_at', $filters['date']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getStats(): array
    {
        return [
            'total'    => $this->query()->whereNull('deleted_at')->count(),
            'success'  => $this->query()->whereNull('deleted_at')->where('status', 'success')->count(),
            'failed'   => $this->query()->whereNull('deleted_at')->where('status', 'failed')->count(),
            'logout'   => $this->query()->whereNull('deleted_at')->where('status', 'logout')->count(),
            'archived' => LoginLog::onlyTrashed()->count(),
        ];
    }

    public function archiveSingle(LoginLog $log): void
    {
        $this->delete($log);
    }

    public function bulkArchive(array $ids): int
    {
        return LoginLog::whereIn('id', $ids)->delete();
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    public function getArchivedPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = LoginLog::onlyTrashed()->latest('logged_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email', 'like', "%{$filters['search']}%")
                    ->orWhere('name',  'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getArchivedTotal(): int
    {
        return LoginLog::onlyTrashed()->count();
    }

    public function restore(int $id): void
    {
        LoginLog::onlyTrashed()->findOrFail($id)->restore();
    }

    public function bulkRestore(array $ids): void
    {
        LoginLog::onlyTrashed()->whereIn('id', $ids)->restore();
    }

    public function forceDelete(int $id): void
    {
        LoginLog::onlyTrashed()->findOrFail($id)->forceDelete();
    }
}
