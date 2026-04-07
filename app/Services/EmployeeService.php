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
    public function getEmployees(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        $user = auth()->user();

        $query = Employee::with(['creator:id,name', 'updater:id,name'])
            ->where('shop_id', $shop->id);

        if ($user->role === 'owner') {
            // Owner sees everyone
        } else {
            // Staff: check their employee_role to determine scope
            $employee = Employee::where('user_id', $user->id)->first();
            $employeeRole = $employee?->roles()->value('role') ?? 'staff';

            if (in_array($employeeRole, ['manager', 'hr'])) {
                // Manager/HR sees everyone except themselves
                $query->where('user_id', '!=', $user->id);
            } else {
                // Everyone else only sees themselves
                $query->where('user_id', $user->id);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function getStats(Shop $shop): array
    {
        return $this->employeeRepository->getStatsByShop($shop);
    }

    public function getBranchNames(Shop $shop): array
    {
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
            $createAccount = !empty($data['create_account']);

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

        if (!empty($changes)) {
            $action = isset($changes['status']) ? 'status_changed' : (isset($changes['salary']) ? 'salary_changed' : 'updated');

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
