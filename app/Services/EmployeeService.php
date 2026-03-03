<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository
    ) {}

    // ── Get all active employees for shop ──────────────────────────────────────

    public function getEmployees(Shop $shop): Collection
    {
        return $this->employeeRepository->getAllByShop($shop);
    }

    // ── Get stats for shop ─────────────────────────────────────────────────────

    public function getStats(Shop $shop): array
    {
        return $this->employeeRepository->getStatsByShop($shop);
    }

    // ── Get branch names for shop ──────────────────────────────────────────────

    public function getBranchNames(Shop $shop): array
    {
        return $this->employeeRepository->getBranchNames($shop);
    }

    // ── Find employee scoped to shop ───────────────────────────────────────────

    public function findEmployee(int $id, Shop $shop): Employee
    {
        return $this->employeeRepository->findByShop($id, $shop);
    }

    // ── Create employee ────────────────────────────────────────────────────────

    public function createEmployee(Shop $shop, array $data): Employee
    {
        return DB::transaction(function () use ($shop, $data) {
            $user = User::create([
                'name'              => $data['first_name'] . ' ' . $data['last_name'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['last_name']),
                'role'              => strtolower($data['position']) === 'manager' ? 'manager' : 'staff',
                'email_verified_at' => now(),
            ]);

            return $this->employeeRepository->createForShop($shop, [
                ...$data,
                'user_id' => $user->id,
            ]);
        });
    }

    // ── Update employee ────────────────────────────────────────────────────────

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        return $this->employeeRepository->updateEmployee($employee, $data);
    }

    // ── Soft delete employee (observer copies to employee_archives) ────────────

    public function archiveEmployee(Employee $employee): void
    {
        $this->employeeRepository->deleteEmployee($employee);
    }

    // ── Verify employee belongs to shop ───────────────────────────────────────

    public function authorizeEmployee(Employee $employee, Shop $shop): void
    {
        if ($employee->shop_id !== $shop->id) {
            abort(403, 'Unauthorized.');
        }
    }

    // ── Import employees from CSV ──────────────────────────────────────────────

    public function importFromCsv(Shop $shop, UploadedFile $file): array
    {
        $errors = [];
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
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

        // Validate headers
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

            [$employee_id, $first_name, $last_name, $phone, $address, $branch_name, $position, $hire_date, $salary, $status] = $data;

            // Basic validation
            if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($position) || empty($hire_date)) {
                $errors[] = "Row {$row}: employee_id, first_name, last_name, position, and hire_date are required.";
                continue;
            }

            if (! in_array($status, ['Active', 'Inactive'])) {
                $errors[] = "Row {$row}: status must be 'Active' or 'Inactive'.";
                continue;
            }

            if (! \Carbon\Carbon::createFromFormat('Y-m-d', $hire_date)) {
                $errors[] = "Row {$row}: hire_date must be in YYYY-MM-DD format.";
                continue;
            }

            // Check duplicate employee_id
            if (Employee::where('employee_id', $employee_id)->exists()) {
                $errors[] = "Row {$row}: Employee ID '{$employee_id}' already exists.";
                continue;
            }

            $shop->employees()->create([
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
            ]);
        }

        fclose($handle);
        return $errors;
    }
}
