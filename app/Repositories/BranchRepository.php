<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BranchRepository extends Repository
{
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    // Branch-specific queries

    public function paginateForShop(int $shopId, array $filters = [], ?string $branch = null): LengthAwarePaginator
    {
        $query = $this->query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->withCount('employees')
            ->where('shop_id', $shopId)
            ->when($branch !== null, fn ($q) => $q->where('name', $branch))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('branch_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('manager_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status));

        $sortBy = $filters['sort_by'] ?? 'branch_code';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        if ($sortBy === 'creator_name') {
            $query->orderBy(
                \App\Models\User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'branches.created_by'),
                $sortDirection,
            );
        } elseif ($sortBy === 'updater_name') {
            $query->orderBy(
                \App\Models\User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'branches.updated_by'),
                $sortDirection,
            );
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function paginateArchivedForShop(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->onlyTrashed()
            ->with(['creator:id,name', 'updater:id,name'])
            ->where('shop_id', $shopId)
            ->latest('deleted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function activeBranchNamesForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->where('status', 'Active')
            ->orderBy('name')
            ->pluck('name');
    }

    public function statsForShop(int $shopId, ?string $branch = null): array
    {
        $base = $this->query()->where('shop_id', $shopId)
            ->when($branch !== null, fn ($q) => $q->where('name', $branch));

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'Active')->count(),
            'inactive' => (clone $base)->where('status', 'Inactive')->count(),
        ];
    }

    public function findTrashedOrFail(int $id): Branch
    {
        return $this->query()->onlyTrashed()->findOrFail($id);
    }

    public function restore(Branch $branch): void
    {
        $branch->restore();
    }
}
