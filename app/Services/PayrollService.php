<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Shop;
use App\Repositories\PayrollRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayrollService
{
    public function __construct(
        private readonly PayrollRepository $payrollRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function paginate(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        return $this->payrollRepository->paginate($shop, $perPage);
    }

    public function find(int $id, Shop $shop): Payroll
    {
        return $this->payrollRepository->find($id, $shop);
    }

    public function generate(Shop $shop, array $data): Payroll
    {
        return DB::transaction(function () use ($shop, $data) {
            $payroll = $this->payrollRepository->create([
                'shop_id'      => $shop->id,
                'period_label' => $data['period_label'],
                'period_start' => $data['period_start'],
                'period_end'   => $data['period_end'],
                'status'       => 'draft',
                'created_by'   => auth()->id(),
            ]);

            $employees = Employee::where('shop_id', $shop->id)
                ->where('status', 'Active')
                ->get();

            foreach ($employees as $employee) {
                $attendance = $this->getAttendanceSummary($employee->id, $data['period_start'], $data['period_end']);
                $item = $this->calculatePayrollItem($employee, $attendance);

                PayrollItem::create([
                    'payroll_id'   => $payroll->id,
                    'employee_id'  => $employee->id,
                    ...$item,
                ]);
            }

            $this->activityLogService->log(
                subject: $payroll,
                action: 'created',
                shopId: $shop->id,
                module: 'Payroll',
            );

            return $payroll->load('items.employee');
        });
    }

    public function updateItem(PayrollItem $item, array $data): PayrollItem
    {
        $item->update([
            'bonuses' => $data['bonuses'] ?? $item->bonuses,
            'remarks' => $data['remarks'] ?? $item->remarks,
        ]);

        $dailyRate  = $this->calculateDailyRate($item->employee);
        $grossPay   = ($dailyRate * $item->days_worked) + (($dailyRate / 2) * $item->days_half_day);
        $netPay     = round($grossPay - (float) $item->deductions + (float) $item->bonuses, 2);

        $item->update(['net_pay' => $netPay]);

        return $item->fresh();
    }

    public function finalize(Payroll $payroll): Payroll
    {
        $payroll->update(['status' => 'finalized']);

        $this->activityLogService->log(
            subject: $payroll,
            action: 'finalized',
            shopId: $payroll->shop_id,
            module: 'Payroll',
        );

        return $payroll;
    }

    public function delete(Payroll $payroll): void
    {
        $this->activityLogService->log(
            subject: $payroll,
            action: 'archived',
            shopId: $payroll->shop_id,
            module: 'Payroll',
        );

        $this->payrollRepository->delete($payroll);
    }

    public function getStats(Shop $shop): array
    {
        $latest = Payroll::where('shop_id', $shop->id)->latest('period_start')->first();

        return [
            'total_payrolls' => Payroll::where('shop_id', $shop->id)->count(),
            'draft'          => Payroll::where('shop_id', $shop->id)->where('status', 'draft')->count(),
            'finalized'      => Payroll::where('shop_id', $shop->id)->where('status', 'finalized')->count(),
            'latest_total'   => $latest ? $latest->items()->sum('net_pay') : 0,
        ];
    }

    /* ─── Private Helpers ─────────────────────────── */

    private function getAttendanceSummary(int $employeeId, string $start, string $end): array
    {
        $base = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end]);

        return [
            'present'  => (clone $base)->where('status', 'present')->count(),
            'absent'   => (clone $base)->where('status', 'absent')->count(),
            'late'     => (clone $base)->where('status', 'late')->count(),
            'half_day' => (clone $base)->where('status', 'half_day')->count(),
        ];
    }

    private function calculateDailyRate(Employee $employee): float
    {
        $monthlySalary = (float) ($employee->salary ?? 0);
        return round($monthlySalary / 26, 2); // 26 working days
    }

    private function calculatePayrollItem(Employee $employee, array $attendance): array
    {
        $dailyRate     = $this->calculateDailyRate($employee);
        $monthlySalary = (float) ($employee->salary ?? 0);

        // Pay based on actual days worked
        $grossPay        = $dailyRate * $attendance['present'];
        $halfDayPay      = ($dailyRate / 2) * $attendance['half_day'];
        $lateDeduction   = ($dailyRate * 0.05) * $attendance['late'];

        $totalGross      = round($grossPay + $halfDayPay, 2);
        $totalDeductions = round($lateDeduction, 2);
        $netPay          = round($totalGross - $totalDeductions, 2);

        return [
            'basic_salary'  => $monthlySalary,
            'days_worked'   => $attendance['present'],
            'days_absent'   => $attendance['absent'],
            'days_late'     => $attendance['late'],
            'days_half_day' => $attendance['half_day'],
            'deductions'    => $totalDeductions,
            'bonuses'       => 0,
            'net_pay'       => $netPay,
        ];
    }

    public function recalculate(Payroll $payroll): Payroll
    {
        return DB::transaction(function () use ($payroll) {
            foreach ($payroll->items as $item) {
                $attendance = $this->getAttendanceSummary(
                    $item->employee_id,
                    $payroll->period_start->toDateString(),
                    $payroll->period_end->toDateString()
                );

                $calculated = $this->calculatePayrollItem($item->employee, $attendance);

                // Keep manual bonuses, recalculate everything else
                $calculated['bonuses'] = (float) $item->bonuses;
                $calculated['net_pay'] = round($calculated['basic_salary'] - $calculated['deductions'] + $calculated['bonuses'], 2);

                $item->update($calculated);
            }

            return $payroll->fresh('items.employee');
        });
    }
}
