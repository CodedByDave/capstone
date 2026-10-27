<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\EmploymentType;
use App\Enums\PayBasis;
use App\Mail\EmployeeCredentialsMail;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        'employment_type',
        'pay_rate',
        'pay_basis',
        'hire_date',
        'status',
    ];

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly ActivityLogService $activityLogService,
        private readonly DriverRosterService $driverRoster,
        private readonly ScheduleService $scheduleService,
        private readonly DefaultRoleProvisioner $roleProvisioner,
    ) {}

    /* ─── READ ─────────────────────────── */

    public function indexData(Shop $shop, array $filters = []): array
    {
        $filters = [
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'employment_type' => $filters['employment_type'] ?? '',
            'sort_by' => $filters['sort_by'] ?? 'employee_id',
            'sort_direction' => $filters['sort_direction'] ?? 'asc',
            'per_page' => (int) ($filters['per_page'] ?? 10),
        ];
        $user = auth()->user();
        $isOwner = $user->role === 'owner';

        return [
            'employees' => $this->employeeRepository->paginateForShop(
                $shop,
                $filters,
                $isOwner ? null : $user->id,
            ),
            'stats' => $this->employeeRepository->getStatsByShop($shop),
            'employment_types' => EmploymentType::options(),
            'pay_bases' => PayBasis::options(),
            'shop' => $shop,
            'filters' => $filters,
        ];
    }

    public function getStats(Shop $shop): array
    {
        return $this->employeeRepository->getStatsByShop($shop);
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
        $schedules = $data['schedules'] ?? [];
        unset($data['schedules']);

        return DB::transaction(function () use ($shop, $data, $schedules) {

            // Always create a login account for every employee.
            $defaultPassword = $data['last_name'];

            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($defaultPassword),
                'role' => AccountType::Staff->value,
                'shop_id' => $shop->id,
                'email_verified_at' => now(),
            ]);

            $role = strtolower($data['position']);

            $employee = $this->employeeRepository->createForShop($shop, [
                ...$data,
                'user_id' => $user->id,
                'has_account' => 1,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->roleProvisioner->provisionRole($shop->id, $role);

            EmployeeRole::firstOrCreate([
                'employee_id' => $employee->id,
                'role' => $role,
            ]);

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

            if ($schedules !== []) {
                $this->scheduleService->syncSchedules($employee, $schedules);
            }

            $this->driverRoster->sync($employee);

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

        if ($createAccount && ! $employee->user_id) {
            $user = User::create([
                'name' => $employee->first_name.' '.$employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make($employee->last_name),
                'role' => AccountType::Staff->value,
                'email_verified_at' => now(),
            ]);

            $updated->update(['user_id' => $user->id, 'has_account' => 1]);

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

        $this->driverRoster->sync($updated);

        if (! empty($changes)) {
            $action = isset($changes['status'])
                ? 'status_changed'
                : (isset($changes['pay_rate']) || isset($changes['pay_basis']) ? 'salary_changed' : 'updated');

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
        $this->driverRoster->sync($employee);
    }

    public function restoreEmployee(Employee $employee): void
    {
        $employee->restore();
        $this->driverRoster->sync($employee);

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
            if (isset($data[$field]) && (string) $employee->$field !== (string) $data[$field]) {
                $changes[$field] = [
                    'old' => (string) $employee->$field,
                    'new' => (string) $data[$field],
                ];
            }
        }

        return $changes;
    }
}
