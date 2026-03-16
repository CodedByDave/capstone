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

    //Branch-specific queries

    public function paginateForShop(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->withCount('employees')
            ->where('shop_id', $shopId)
            ->latest()
            ->paginate($perPage)
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

    public function statsForShop(int $shopId): array
    {
        $base = $this->query()->where('shop_id', $shopId);

        return [
            'total'    => (clone $base)->count(),
            'active'   => (clone $base)->where('status', 'Active')->count(),
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
