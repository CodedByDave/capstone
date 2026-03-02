<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Employee());
    }

    // ── Get all active employees scoped to shop ────────────────────────────────

    public function getAllByShop(Shop $shop): Collection
    {
        return $shop->employees()->latest()->get();
    }

    // ── Stats scoped to shop ───────────────────────────────────────────────────

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

    // ── Find active employee scoped to shop ────────────────────────────────────

    public function findByShop(int $id, Shop $shop): Employee
    {
        return $shop->employees()->findOrFail($id);
    }

    // ── Create employee under shop ─────────────────────────────────────────────

    public function createForShop(Shop $shop, array $data): Employee
    {
        return $this->transaction(function () use ($shop, $data) {
            return $shop->employees()->create($data);
        });
    }

    // ── Update employee ────────────────────────────────────────────────────────

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $this->transaction(function () use ($employee, $data) {
            $this->update($employee, $data);
        });

        return $employee->fresh();
    }

    // ── Soft delete employee (triggers observer → copies to employee_archives) ─

    public function deleteEmployee(Employee $employee): void
    {
        $this->delete($employee);
    }

    // ── Get unique branch names for shop ───────────────────────────────────────

    public function getBranchNames(Shop $shop): array
    {
        $branches = $shop->employees()
            ->whereNotNull('branch_name')
            ->distinct()
            ->pluck('branch_name')
            ->toArray();

        if ($shop->branch_name && ! in_array($shop->branch_name, $branches)) {
            array_unshift($branches, $shop->branch_name);
        }

        return $branches;
    }
}
