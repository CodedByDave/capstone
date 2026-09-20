<?php

namespace App\Repositories;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends Repository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query()
            ->whereNull('deleted_at')
            ->where('role', '!=', AccountType::SuperAdmin->value)
            ->with('shop');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['verified'])) {
            $filters['verified'] === 'yes'
                ? $query->whereNotNull('email_verified_at')
                : $query->whereNull('email_verified_at');
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'shop_name') {
            $query->orderBy(
                \App\Models\Shop::select('shop_name')
                    ->whereColumn('shops.owner_id', 'users.id')
                    ->limit(1),
                $sortDirection,
            );
        } else {
            $sortableColumns = ['name', 'email', 'created_at'];
            $query->orderBy(
                in_array($sortBy, $sortableColumns, true) ? $sortBy : 'created_at',
                $sortDirection,
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getStats(): array
    {
        return [
            'total' => User::whereNull('deleted_at')->where('role', '!=', AccountType::SuperAdmin->value)->count(),
            'owners' => User::whereNull('deleted_at')->where('role', AccountType::ShopOwner->value)->count(),
            'staff' => User::whereNull('deleted_at')->where('role', AccountType::Staff->value)->count(),
            'users' => User::whereNull('deleted_at')->where('role', AccountType::Customer->value)->count(),
            'verified' => User::whereNull('deleted_at')
                ->where('role', '!=', AccountType::SuperAdmin->value)
                ->whereNotNull('email_verified_at')
                ->count(),
            'archived' => User::onlyTrashed()->where('role', '!=', AccountType::SuperAdmin->value)->count(),
        ];
    }

    public function getForCsvExport(): Collection
    {
        return User::query()
            ->whereNull('deleted_at')
            ->where('role', '!=', AccountType::SuperAdmin->value)
            ->orderBy('id')
            ->get();
    }

    public function findWithRelations(int $id): User
    {
        return User::with('shop')->findOrFail($id);
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    public function archiveUser(User $user): void
    {
        $this->delete($user);
    }

    public function bulkArchive(array $ids): void
    {
        User::whereIn('id', $ids)
            ->where('role', '!=', AccountType::SuperAdmin->value)
            ->delete();
    }

    public function getArchivedPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = User::onlyTrashed()
            ->where('role', '!=', AccountType::SuperAdmin->value)
            ->with('shop')
            ->latest();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getArchivedTotal(): int
    {
        return User::onlyTrashed()
            ->where('role', '!=', AccountType::SuperAdmin->value)
            ->count();
    }

    public function restore(int $id): void
    {
        User::onlyTrashed()->findOrFail($id)->restore();
    }

    public function bulkRestore(array $ids): void
    {
        User::onlyTrashed()->whereIn('id', $ids)->restore();
    }

    public function forceDelete(int $id): void
    {
        User::onlyTrashed()->findOrFail($id)->forceDelete();
    }
}
