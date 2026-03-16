<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\BulkEmployeeRequest;
use App\Http\Requests\Shop\StoreEmployeeRequest;
use App\Http\Requests\Shop\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\Shop;
use App\Models\ShopRole;
use App\Repositories\BranchRepository;
use App\Services\EmployeeService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService  $employeeService,
        private readonly BranchRepository $branchRepository,
    ) {}

    /* ─── PRIVATE HELPERS ────────────────────────────────────── */

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        // Staff — resolve shop via their employee record
        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    private function isOwner(): bool
    {
        return auth()->user()->role === 'owner';
    }

    private function redirectIndex()
    {
        return redirect()->route(
            $this->isOwner() ? 'employee.index' : 'staff.employee.index'
        );
    }

    private function getRoles(Shop $shop): array
    {
        return ShopRole::where('shop_id', $shop->id)
            ->where('name', '!=', 'owner')
            ->pluck('name')
            ->values()
            ->toArray();
    }

    /* ─── READ ───────────────────────────────────────────────── */

    public function index(Request $request)
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/Index', [
            'employees'    => $this->employeeService->getEmployees($shop, $request->integer('perPage', 10)),
            'stats'        => $this->employeeService->getStats($shop),
            'branch_names' => $this->employeeService->getBranchNames($shop),
            'shop'         => $shop,
        ]);
    }

    public function show(Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);

        return Inertia::render('shop/employee/Show', [
            'employee' => $employee->load(['creator:id,name', 'updater:id,name']),
            'schedule' => $employee->schedule,
        ]);
    }

    public function create()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/Create', [
            'roles'        => $this->getRoles($shop),
            'branch_names' => $this->employeeService->getBranchNames($shop),
            'shop'         => $shop,
        ]);
    }

    public function edit(Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);

        return Inertia::render('shop/employee/Edit', [
            'employee'     => $employee,
            'branch_names' => $this->employeeService->getBranchNames($shop),
            'roles'        => $this->getRoles($shop),
        ]);
    }

    public function archive()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/Archive', [
            'archived' => EmployeeArchive::where('shop_id', $shop->id)
                ->latest('archived_at')
                ->get(),

            'archivedBranches' => $this->branchRepository
                ->paginateArchivedForShop($shop->id, perPage: 999)
                ->items(),
        ]);
    }

    /* ─── WRITE ──────────────────────────────────────────────── */

    public function store(StoreEmployeeRequest $request)
    {
        $shop = $this->getShop();
        $this->employeeService->createEmployee($shop, $request->validated());

        return $this->redirectIndex()
            ->with('toast', ['type' => 'success', 'message' => 'Employee added successfully.']);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);
        $this->employeeService->updateEmployee($employee, $request->validated());

        return $this->redirectIndex()
            ->with('toast', ['type' => 'success', 'message' => 'Employee updated successfully.']);
    }

    public function destroy(Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);
        $this->employeeService->archiveEmployee($employee);

        return redirect()->route('employee.archive')
            ->with('toast', ['type' => 'success', 'message' => 'Employee archived successfully.']);
    }

    public function restore(int $id)
    {
        $shop     = $this->getShop();
        $employee = $shop->employees()->onlyTrashed()->findOrFail($id);
        $this->employeeService->restoreEmployee($employee);

        return redirect()->route('employee.archive')
            ->with('toast', ['type' => 'success', 'message' => 'Employee restored successfully.']);
    }

    public function bulkRestore(BulkEmployeeRequest $request)
    {
        $shop      = $this->getShop();
        $employees = $shop->employees()->onlyTrashed()->whereIn('id', $request->ids)->get();

        foreach ($employees as $employee) {
            $this->employeeService->restoreEmployee($employee);
        }

        return redirect()->route('employee.archive')
            ->with('toast', [
                'type'    => 'success',
                'message' => count($employees) . ' employee(s) restored successfully.',
            ]);
    }

    /* ─── IMPORT ─────────────────────────────────────────────── */

    public function importTemplate()
    {
        $columns = [
            'employee_id', 'first_name', 'last_name', 'email',
            'phone', 'address', 'branch_name', 'position',
            'hire_date', 'salary', 'status',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt']]);

        $shop   = $this->getShop();
        $errors = $this->employeeService->importFromCsv($shop, $request->file('csv_file'));

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return response()->json(['message' => 'Import successful.']);
    }
}
