<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    // Fields that get tracked on update
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

    /* ─── READ ─────────────────────────────────────── */

    public function getEmployees(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        $user = auth()->user();

        $query = Employee::query()
            ->where('shop_id', $shop->id);

        // OWNER → all
        if ($user->role === 'owner') {
            // no restriction
        }

        // MANAGER
        elseif ($user->role === 'manager') {
            $query->where('branch_name', $user->employee->branch_name)
            ->where('user_id', '!=', $user->id);
        }

        // HR
        elseif ($user->role === 'hr') {
            $query->where('branch_name', $user->employee->branch_name)
                ->where('user_id', '!=', $user->id);
        }

        // STAFF
        else {
            $query->where('user_id', $user->id);
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

    /* ─── WRITE ─────────────────────────────────────── */

    public function createEmployee(Shop $shop, array $data): Employee
    {
        return DB::transaction(function () use ($shop, $data) {
            $user = User::create([
                'name'              => "{$data['first_name']} {$data['last_name']}",
                'email'             => $data['email'],
                'password'          => Hash::make($data['last_name']),
                'role'              => 'staff',
                'email_verified_at' => now(),
            ]);

            $employee = $this->employeeRepository->createForShop($shop, [
                ...$data,
                'user_id'    => $user->id,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Centralized log — no EmployeeActivityLog here
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
        $shopId  = $employee->shop_id;

        $updated = $this->employeeRepository->updateEmployee($employee, [
            ...$data,
            'updated_by' => auth()->id(),
        ]);

        if (!empty($changes)) {
            $action = match (true) {
                isset($changes['status']) => 'status_changed',
                isset($changes['salary']) => 'salary_changed',
                default                   => 'updated',
            };

            $log = $this->activityLogService->log(
                subject: $updated,
                action: $action,
                changes: $changes,
                shopId: $shopId,
            );
        } else {
            dd('changes was empty — nothing logged');
        }

        return $updated;
    }

    public function archiveEmployee(Employee $employee): void
    {
        // Log BEFORE delete so subject still exists
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

    /* ─── IMPORT ─────────────────────────────────────── */

    public function importFromCsv(Shop $shop, UploadedFile $file): array
    {
        $errors  = [];
        $handle  = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        $expectedHeaders = [
            'employee_id',
            'first_name',
            'last_name',
            'phone',
            'address',
            'branch_name',
            'position',
            'hire_date',
            'salary',
            'status',
        ];

        if (array_map('strtolower', array_map('trim', $headers)) !== $expectedHeaders) {
            fclose($handle);
            return ['Invalid CSV headers. Please use the provided template.'];
        }

        $row = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            if (count($data) !== count($expectedHeaders)) {
                $errors[] = "Row {$row}: Invalid number of columns.";
                continue;
            }

            [
                $employee_id,
                $first_name,
                $last_name,
                $phone,
                $address,
                $branch_name,
                $position,
                $hire_date,
                $salary,
                $status
            ] = $data;

            if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($position) || empty($hire_date)) {
                $errors[] = "Row {$row}: employee_id, first_name, last_name, position, and hire_date are required.";
                continue;
            }

            if (!in_array($status, ['Active', 'Inactive'])) {
                $errors[] = "Row {$row}: status must be 'Active' or 'Inactive'.";
                continue;
            }

            if (!\Carbon\Carbon::createFromFormat('Y-m-d', $hire_date)) {
                $errors[] = "Row {$row}: hire_date must be in YYYY-MM-DD format.";
                continue;
            }

            if (Employee::where('employee_id', $employee_id)->exists()) {
                $errors[] = "Row {$row}: Employee ID '{$employee_id}' already exists.";
                continue;
            }

            $employee = $shop->employees()->create([
                'user_id'     => auth()->id(),
                'employee_id' => $employee_id,
                'first_name'  => $first_name,
                'last_name'   => $last_name,
                'phone'       => $phone ?: null,
                'address'     => $address ?: null,
                'branch_name' => $branch_name ?: null,
                'position'    => $position,
                'hire_date'   => $hire_date,
                'salary'      => $salary ?: null,
                'status'      => $status,
                'created_by'  => auth()->id(),
                'updated_by'  => auth()->id(),
            ]);

            // Log each imported employee
            $this->activityLogService->log(
                subject: $employee,
                action: 'created',
                shopId: $shop->id,
            );
        }

        fclose($handle);
        return $errors;
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
