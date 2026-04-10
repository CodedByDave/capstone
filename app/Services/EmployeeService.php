<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shop;
use App\Models\User;
use App\Models\ShopRole;
use App\Models\EmployeeRole;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Mail\EmployeeCredentialsMail;
use Illuminate\Support\Facades\Mail;

class EmployeeService
{
    private const TRACKED_FIELDS = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'position',
        'branch_name',
        'hire_date',
        'salary',
        'status',
    ];

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /* ─── READ ─────────────────────────── */

    /**
     * Returns the authenticated staff member's branch name, or null for owners.
     * An empty branch_name is treated as "unassigned" (returns null).
     */
    private function getStaffBranch(): ?string
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return null;
        }

        $branch = Employee::where('user_id', $user->id)->value('branch_name');

        return ($branch !== null && $branch !== '') ? $branch : null;
    }

    public function getEmployees(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        $query = Employee::with(['creator:id,name', 'updater:id,name'])
            ->where('shop_id', $shop->id);

        $branch = $this->getStaffBranch();

        if (auth()->user()->role !== 'owner') {
            if ($branch !== null) {
                // Show only the manager's branch, excluding their own record
                $query->where('branch_name', $branch)
                      ->where(function ($q) {
                          $q->where('user_id', '!=', auth()->id())
                            ->orWhereNull('user_id');
                      });
            } else {
                // No branch assigned — return empty list
                $query->whereRaw('1 = 0');
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function getStats(Shop $shop): array
    {
        $branch = $this->getStaffBranch(); // null for owners → no filter applied

        return $this->employeeRepository->getStatsByShop($shop, $branch);
    }

    public function getBranchNames(Shop $shop): array
    {
        $branch = $this->getStaffBranch();

        // Non-owners (managers/staff) can only assign to their own branch
        if (auth()->user()->role !== 'owner') {
            return $branch !== null ? [$branch] : [];
        }

        return $this->employeeRepository->getBranchNames($shop);
    }

    public function findEmployee(int $id, Shop $shop): Employee
    {
        return $this->employeeRepository->findByShop($id, $shop);
    }

    public function authorizeEmployee(Employee $employee, Shop $shop): void
    {
        abort_if($employee->shop_id !== $shop->id, 403, 'Unauthorized.');
    }

    /* ─── WRITE ─────────────────────────── */

    public function createEmployee(Shop $shop, array $data): Employee
    {
        return DB::transaction(function () use ($shop, $data) {

            $userId = null;
            $createAccount = (bool) ($data['create_account'] ?? false);

            if ($createAccount) {
                $defaultPassword = $data['last_name'];

                $user = User::create([
                    'name'              => $data['first_name'] . ' ' . $data['last_name'],
                    'email'             => $data['email'],
                    'password'          => Hash::make($defaultPassword),
                    'role'              => 'staff',
                    'email_verified_at' => now(),
                ]);

                $userId = $user->id;
            }

            $role = strtolower($data['position']);

            $employee = $this->employeeRepository->createForShop($shop, [
                ...$data,
                'user_id'    => $userId,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            ShopRole::firstOrCreate(
                ['shop_id' => $shop->id, 'name' => $role],
                ['is_default' => true]
            );

            EmployeeRole::firstOrCreate([
                'employee_id' => $employee->id,
                'role'        => $role,
            ]);

            // Only send credentials if account was created
            if ($createAccount) {
                try {
                    Mail::to($user->email)->send(new EmployeeCredentialsMail(
                        name: $user->name,
                        email: $user->email,
                        password: $defaultPassword,
                        shopName: $shop->shop_name,
                    ));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send employee credentials email', [
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->activityLogService->log(
                subject: $employee,
                action: 'created',
                shopId: $shop->id,
            );

            return $employee;
        });
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $changes = $this->diffChanges($employee, $data);

        $updated = $this->employeeRepository->updateEmployee($employee, [
            ...$data,
            'updated_by' => auth()->id(),
        ]);

        // Create system account only if explicitly requested and none exists yet
        $createAccount = (int) ($data['create_account'] ?? 0) === 1;

        if ($createAccount && !$employee->user_id) {
            $user = User::create([
                'name'              => $employee->first_name . ' ' . $employee->last_name,
                'email'             => $employee->email,
                'password'          => Hash::make($employee->last_name),
                'role'              => 'staff',
                'email_verified_at' => now(),
            ]);

            $updated->update(['user_id' => $user->id]);

            try {
                Mail::to($user->email)->send(new EmployeeCredentialsMail(
                    name: $user->name,
                    email: $user->email,
                    password: $employee->last_name,
                    shopName: $employee->shop->shop_name,
                ));
            } catch (\Exception $e) {
                \Log::warning('Failed to send employee credentials email', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($changes)) {
            $action = isset($changes['status'])
                ? 'status_changed'
                : (isset($changes['salary']) ? 'salary_changed' : 'updated');

            $this->activityLogService->log(
                subject: $updated,
                action: $action,
                changes: $changes,
                shopId: $employee->shop_id,
            );
        }

        return $updated;
    }

    public function archiveEmployee(Employee $employee): void
    {
        $this->activityLogService->log(
            subject: $employee,
            action: 'archived',
            shopId: $employee->shop_id,
        );

        $this->employeeRepository->deleteEmployee($employee);
    }

    public function restoreEmployee(Employee $employee): void
    {
        $employee->restore();

        $this->activityLogService->log(
            subject: $employee,
            action: 'restored',
            shopId: $employee->shop_id,
        );
    }

    /* ─── PRIVATE HELPERS ────────────────────────────── */

    private function diffChanges(Employee $employee, array $data): array
    {
        $changes = [];
        foreach (self::TRACKED_FIELDS as $field) {
            if (isset($data[$field]) && (string)$employee->$field !== (string)$data[$field]) {
                $changes[$field] = [
                    'old' => (string)$employee->$field,
                    'new' => (string)$data[$field],
                ];
            }
        }
        return $changes;
    }

    private function roleForPosition(string $position): string
    {
        return strtolower($position);
    }
}
