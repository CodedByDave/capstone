<?php
// app/Http/Controllers/Shop/Employee/PayrollController.php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Employee\GeneratePayrollRequest;
use App\Http\Requests\Shop\Employee\UpdatePayrollItemRequest;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Shop;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollService $payrollService,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    private function base(): string
    {
        return auth()->user()->role === 'owner' ? '/shop' : '/staff';
    }

    public function index()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/payroll/Index', [
            'payrolls' => $this->payrollService->paginate($shop),
            'stats'    => $this->payrollService->getStats($shop),
            'settings' => [
                'deduct_sss'             => (bool) $shop->deduct_sss,
                'deduct_philhealth'      => (bool) $shop->deduct_philhealth,
                'deduct_pagibig'         => (bool) $shop->deduct_pagibig,
                'deduct_withholding_tax' => (bool) $shop->deduct_withholding_tax,
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $shop = $this->getShop();
        abort_if($request->user()->role !== 'owner', 403);

        $this->payrollService->updateSettings($shop, $request->only([
            'deduct_sss', 'deduct_philhealth', 'deduct_pagibig', 'deduct_withholding_tax',
        ]));

        return back()->with('toast', ['type' => 'success', 'message' => 'Payroll settings saved.']);
    }

    public function show(Payroll $payroll)
    {
        $shop = $this->getShop();
        abort_if($payroll->shop_id !== $shop->id, 403);

        $payroll->load(['items.employee:id,employee_id,first_name,last_name,position,branch_name,salary', 'creator:id,name']);

        return Inertia::render('shop/employee/payroll/Show', [
            'payroll' => $payroll,
        ]);
    }

    public function store(GeneratePayrollRequest $request)
    {
        $shop = $this->getShop();

        $existing = Payroll::where('shop_id', $shop->id)
            ->where('period_start', $request->period_start)
            ->where('period_end', $request->period_end)
            ->exists();

        if ($existing) {
            return back()->withErrors(['period_start' => 'A payroll for this period already exists.']);
        }

        $this->payrollService->generate($shop, $request->validated());

        return redirect("{$this->base()}/payroll")
            ->with('toast', ['type' => 'success', 'message' => 'Payroll generated successfully.']);
    }

    public function updateItem(UpdatePayrollItemRequest $request, PayrollItem $payrollItem)
    {
        $shop = $this->getShop();
        abort_if($payrollItem->payroll->shop_id !== $shop->id, 403);
        abort_if($payrollItem->payroll->status === 'finalized', 403, 'Cannot edit a finalized payroll.');

        $this->payrollService->updateItem($payrollItem, $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Payroll item updated.']);
    }

    public function recalculate(Payroll $payroll)
    {
        $shop = $this->getShop();
        abort_if($payroll->shop_id !== $shop->id, 403);
        abort_if($payroll->status === 'finalized', 403, 'Cannot recalculate a finalized payroll.');

        $this->payrollService->recalculate($payroll);

        return back()->with('toast', ['type' => 'success', 'message' => 'Payroll recalculated.']);
    }

    public function finalize(Payroll $payroll)
    {
        $shop = $this->getShop();
        abort_if($payroll->shop_id !== $shop->id, 403);
        abort_if($payroll->status === 'finalized', 403, 'Already finalized.');

        $this->payrollService->finalize($payroll);

        return back()->with('toast', ['type' => 'success', 'message' => 'Payroll finalized.']);
    }

    public function destroy(Payroll $payroll)
    {
        $shop = $this->getShop();
        abort_if($payroll->shop_id !== $shop->id, 403);
        abort_if($payroll->status === 'finalized', 403, 'Cannot delete a finalized payroll.');

        $this->payrollService->delete($payroll);

        return redirect("{$this->base()}/payroll")
            ->with('toast', ['type' => 'success', 'message' => 'Payroll deleted.']);
    }
}
