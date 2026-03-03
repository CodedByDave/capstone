<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\BulkEmployeeRequest;
use App\Http\Requests\Shop\StoreEmployeeRequest;
use App\Http\Requests\Shop\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\Shop;
use App\Services\EmployeeService;
use Inertia\Inertia;
use Illuminate\Http\Request;
class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {}

    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    // Index

    public function index()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/Index', [
            'employees'    => $this->employeeService->getEmployees($shop),
            'stats'        => $this->employeeService->getStats($shop),
            'branch_names' => $this->employeeService->getBranchNames($shop),
            'shop'         => $shop,
        ]);
    }

    // Create

    public function create()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/Create', [
            'branch_names' => $this->employeeService->getBranchNames($shop),
            'shop'         => $shop,
        ]);
    }

    // Store

    public function store(StoreEmployeeRequest $request)
    {
        $shop = $this->getShop();

        $this->employeeService->createEmployee($shop, $request->validated());

        return redirect()->route('employee.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Employee Added successfully'
            ]);
    }

    // Show

    public function show(Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);

        return Inertia::render('shop/employee/Show', [
            'employee' => $employee,
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function edit(Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);

        return Inertia::render('shop/employee/Edit', [
            'employee'     => $employee,
            'branch_names' => $this->employeeService->getBranchNames($shop),
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);

        $this->employeeService->updateEmployee($employee, $request->validated());

        return redirect()->route('employee.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    // ── Destroy (soft delete → archive) ───────────────────────────────────────

    public function destroy(Employee $employee)
    {
        $shop = $this->getShop();
        $this->employeeService->authorizeEmployee($employee, $shop);

        $this->employeeService->archiveEmployee($employee);

        return redirect()->route('employee.index')
            ->with('success', 'Employee archived successfully.');
    }

    // ── Archive list ───────────────────────────────────────────────────────────

    public function archive()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/Archive', [
            'archived' => EmployeeArchive::where('shop_id', $shop->id)
                ->latest('archived_at')
                ->get(),
        ]);
    }

    // ── Restore single ─────────────────────────────────────────────────────────

    public function restore(int $id)
    {
        $shop     = $this->getShop();
        $employee = $shop->employees()->onlyTrashed()->findOrFail($id);
        $employee->restore(); // Observer removes from employee_archives automatically

        return redirect()->back()->with('success', 'Employee restored successfully.');
    }

    // ── Bulk restore ───────────────────────────────────────────────────────────

    public function bulkRestore(BulkEmployeeRequest $request)
    {
        $shop      = $this->getShop();
        $employees = $shop->employees()->onlyTrashed()->whereIn('id', $request->ids)->get();

        foreach ($employees as $employee) {
            $employee->restore(); // Observer fires for each one
        }

        return redirect()->back()->with('success', count($employees) . ' employee(s) restored successfully.');
    }

    // ── Import template ────────────────────────────────────────────────────────

    public function importTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ];

        $columns = [
            'employee_id', 'first_name', 'last_name', 'email', 'phone',
            'address', 'branch_name', 'position', 'hire_date', 'salary', 'status',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Import ─────────────────────────────────────────────────────────────────

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $shop   = $this->getShop();
        $errors = $this->employeeService->importFromCsv($shop, $request->file('csv_file'));

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return response()->json(['message' => 'Import successful.']);
    }
}
