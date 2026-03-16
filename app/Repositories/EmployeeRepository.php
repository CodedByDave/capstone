<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Shop;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Employee());
    }

    // Get all active employees scoped to shop

    public function getAllByShop(Shop $shop): Collection
    {
        return $shop->employees()
            ->with(['creator:id,name', 'updater:id,name'])
            ->latest()
            ->get();
    }

    // Stats scoped to shop

    public function getStatsByShop(Shop $shop): array
    {
        $query = $shop->employees();

        return [
            'total'          => (clone $query)->count(),
            'active'         => (clone $query)->active()->count(),
            'inactive'       => (clone $query)->inactive()->count(),
            'new_this_month' => (clone $query)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];
    }

    // Find active employee scoped to shop

    public function findByShop(int $id, Shop $shop): Employee
    {
        return $shop->employees()->findOrFail($id);
    }

    // Create employee under shop

    public function createForShop(Shop $shop, array $data): Employee
    {
        return $this->transaction(function () use ($shop, $data) {
            return $shop->employees()->create($data);
        });
    }

    // Update employee

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $this->transaction(function () use ($employee, $data) {
            $this->update($employee, $data);
        });

        return $employee->fresh();
    }

    // Soft delete employee

    public function deleteEmployee(Employee $employee): void
    {
        $this->delete($employee);
    }

    // ── Get unique branch names for shop ───────────────────────────────────────

    public function getBranchNames(Shop $shop): array
    {
        return Branch::where('shop_id', $shop->id)
            ->where('status', 'Active')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    //Pagination
    public function getPaginatedByShop(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        return $shop->employees()
            ->with(['creator:id,name', 'updater:id,name'])
            ->latest()
            ->paginate($perPage);
    }
}
