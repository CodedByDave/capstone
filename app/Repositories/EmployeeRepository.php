<?php

namespace App\Repositories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Employee);
    }

    // Get all active employees scoped to shop

    public function getAllByShop(Shop $shop): Collection
    {
        return $shop->employees()
            ->with(['creator:id,name', 'updater:id,name'])
            ->latest()
            ->get();
    }

    // Stats scoped to shop (and optionally to a specific branch)

    public function getStatsByShop(Shop $shop, ?string $branch = null): array
    {
        $query = $shop->employees();

        if ($branch !== null && $branch !== '') {
            $query->where('branch_name', $branch);
        }

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->active()->count(),
            'inactive' => (clone $query)->inactive()->count(),
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

    // Update employee — strips service-only keys before hitting the DB

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        // Keys that exist in the service layer but are NOT columns on the employees table
        $serviceOnlyKeys = ['create_account'];

        $dbData = array_diff_key($data, array_flip($serviceOnlyKeys));

        $this->transaction(function () use ($employee, $dbData) {
            $this->update($employee, $dbData);
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

    // Pagination

    public function getPaginatedByShop(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        return $shop->employees()
            ->with(['creator:id,name', 'updater:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    public function branchNameForUser(int $userId): ?string
    {
        return $this->query()
            ->where('user_id', $userId)
            ->value('branch_name');
    }

    public function activeManagerOptionsForShop(int $shopId): array
    {
        return $this->query()
            ->select(['id', 'employee_id', 'first_name', 'last_name'])
            ->where('shop_id', $shopId)
            ->where('status', 'Active')
            ->whereRaw('LOWER(position) = ?', ['manager'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->full_name,
            ])
            ->all();
    }

    public function paginateForShop(
        Shop $shop,
        array $filters = [],
        ?string $branchScope = null,
        ?int $excludeUserId = null,
    ): LengthAwarePaginator {
        $query = $this->query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->where('shop_id', $shop->id)
            ->when($branchScope !== null, fn ($query) => $query->where('branch_name', $branchScope))
            ->when($excludeUserId !== null, function ($query) use ($excludeUserId) {
                $query->where(function ($query) use ($excludeUserId) {
                    $query->where('user_id', '!=', $excludeUserId)
                        ->orWhereNull('user_id');
                });
            })
            ->when($filters['search'] ?? null, function ($query, string $search) {
                foreach (preg_split('/\s+/', trim($search)) ?: [] as $term) {
                    $query->where(function ($query) use ($term) {
                        $query
                            ->where('employee_id', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('position', 'like', "%{$term}%")
                            ->orWhere('employment_type', 'like', "%{$term}%")
                            ->orWhere('pay_basis', 'like', "%{$term}%")
                            ->orWhere('branch_name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('address', 'like', "%{$term}%");
                    });
                }
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['branch'] ?? null, fn ($query, string $branch) => $query->where('branch_name', $branch))
            ->when(
                $filters['employment_type'] ?? null,
                fn ($query, string $type) => $query->where('employment_type', $type),
            );

        $sortBy = $filters['sort_by'] ?? 'employee_id';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        if ($sortBy === 'full_name') {
            $query->orderBy('first_name', $sortDirection)
                ->orderBy('last_name', $sortDirection);
        } elseif ($sortBy === 'creator_name') {
            $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'employees.created_by'),
                $sortDirection,
            );
        } elseif ($sortBy === 'updater_name') {
            $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'employees.updated_by'),
                $sortDirection,
            );
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }
}
