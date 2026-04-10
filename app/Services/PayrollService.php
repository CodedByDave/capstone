<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Shop;
use App\Repositories\PayrollRepository;
use DateTime;
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

            $employeeQuery = Employee::where('shop_id', $shop->id)
                ->where('status', 'Active');

            // Non-owners generate payroll only for their branch, excluding themselves
            if (auth()->user()->role !== 'owner') {
                $userId = auth()->id();
                $branch = Employee::where('user_id', $userId)->value('branch_name');

                if ($branch) {
                    $employeeQuery->where('branch_name', $branch)
                        ->where(function ($q) use ($userId) {
                            $q->where('user_id', '!=', $userId)
                              ->orWhereNull('user_id');
                        });
                } else {
                    $employeeQuery->whereRaw('1 = 0');
                }
            }

            $employees = $employeeQuery->get();

            foreach ($employees as $employee) {
                $attendance = $this->getAttendanceSummary($employee, $data['period_start'], $data['period_end']);
                $item = $this->calculatePayrollItem($employee, $attendance, $data['period_start'], $data['period_end'], $shop);

                PayrollItem::create([
                    'payroll_id'  => $payroll->id,
                    'employee_id' => $employee->id,
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

        $dailyRate = $this->calculateDailyRate($item->employee);
        $grossPay  = ($dailyRate * $item->days_worked) + (($dailyRate / 2) * $item->days_half_day);

        $netPay = max(0, round(
            $grossPay
            - (float) $item->deductions
            - (float) $item->sss_contribution
            - (float) $item->philhealth_contribution
            - (float) $item->pagibig_contribution
            - (float) $item->withholding_tax
            + (float) $item->bonuses,
            2
        ));

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

    public function updateSettings(Shop $shop, array $data): void
    {
        $shop->update([
            'deduct_sss'             => $data['deduct_sss'] ?? false,
            'deduct_philhealth'      => $data['deduct_philhealth'] ?? false,
            'deduct_pagibig'         => $data['deduct_pagibig'] ?? false,
            'deduct_withholding_tax' => $data['deduct_withholding_tax'] ?? false,
        ]);
    }

    public function recalculate(Payroll $payroll): Payroll
    {
        return DB::transaction(function () use ($payroll) {
            $payroll->load('items.employee');

            foreach ($payroll->items as $item) {
                $attendance = $this->getAttendanceSummary(
                    $item->employee,
                    $payroll->period_start->toDateString(),
                    $payroll->period_end->toDateString()
                );

                $calculated = $this->calculatePayrollItem(
                    $item->employee,
                    $attendance,
                    $payroll->period_start->toDateString(),
                    $payroll->period_end->toDateString(),
                    $payroll->shop
                );

                // Keep manual bonuses, recalculate everything else
                $calculated['bonuses'] = (float) $item->bonuses;

                $dailyRate = $this->calculateDailyRate($item->employee);
                $grossPay  = ($dailyRate * $calculated['days_worked']) + (($dailyRate / 2) * $calculated['days_half_day']);

                $calculated['net_pay'] = max(0, round(
                    $grossPay
                    - $calculated['deductions']
                    - $calculated['sss_contribution']
                    - $calculated['philhealth_contribution']
                    - $calculated['pagibig_contribution']
                    - $calculated['withholding_tax']
                    + $calculated['bonuses'],
                    2
                ));

                $item->update($calculated);
            }

            return $payroll->fresh('items.employee');
        });
    }

    /* ─── Private Helpers ─────────────────────────────────────────────────── */

    /**
     * Count attendance statuses within the period.
     * Only rows explicitly recorded in the attendance table are counted.
     * Absent days reduce pay naturally since gross is based only on present/late/half_day days.
     */
    private function getAttendanceSummary(Employee $employee, string $start, string $end): array
    {
        $base = Attendance::where('employee_id', $employee->id)
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

    /**
     * Returns 0.5 for semi-monthly periods, 1.0 for full-month periods.
     * Used to pro-rate monthly government contributions.
     */
    private function getPeriodFactor(string $start, string $end): float
    {
        $days = (new DateTime($start))->diff(new DateTime($end))->days + 1;
        return $days >= 25 ? 1.0 : 0.5;
    }

    /* ─── Philippine Government Contributions ─────────────────────────────── */

    /**
     * SSS employee share (4.5% of Monthly Salary Credit).
     * MSC range: ₱4,000 – ₱30,000, rounded to nearest ₱500.
     */
    private function calculateSSS(float $monthlySalary): float
    {
        $msc = max(4000, min(30000, round($monthlySalary / 500) * 500));
        return round($msc * 0.045, 2);
    }

    /**
     * PhilHealth employee share (2.5% of monthly basic salary).
     * Minimum: ₱250 | Maximum: ₱2,500
     */
    private function calculatePhilHealth(float $monthlySalary): float
    {
        return round(min(2500.0, max(250.0, $monthlySalary * 0.025)), 2);
    }

    /**
     * Pag-IBIG employee share.
     * ≤ ₱1,500: 1% | > ₱1,500: 2% (max ₱100)
     */
    private function calculatePagIbig(float $monthlySalary): float
    {
        if ($monthlySalary <= 1500) {
            return round($monthlySalary * 0.01, 2);
        }
        return round(min(100.0, $monthlySalary * 0.02), 2);
    }

    /**
     * BIR withholding tax using semi-monthly TRAIN Law brackets.
     * Taxable income = gross pay for the period − mandatory contributions.
     */
    private function calculateWithholdingTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 10416.67) return 0.0;
        if ($taxableIncome <= 16666.67) return round(($taxableIncome - 10416.67) * 0.15, 2);
        if ($taxableIncome <= 33333.33) return round(937.50  + ($taxableIncome - 16666.67) * 0.20, 2);
        if ($taxableIncome <= 83333.33) return round(4270.83 + ($taxableIncome - 33333.33) * 0.25, 2);
        if ($taxableIncome <= 333333.33) return round(16770.83 + ($taxableIncome - 83333.33) * 0.30, 2);
        return round(91770.83 + ($taxableIncome - 333333.33) * 0.35, 2);
    }

    private function calculatePayrollItem(Employee $employee, array $attendance, string $periodStart, string $periodEnd, Shop $shop): array
    {
        $dailyRate     = $this->calculateDailyRate($employee);
        $monthlySalary = (float) ($employee->salary ?? 0);
        $periodFactor  = $this->getPeriodFactor($periodStart, $periodEnd);

        // Gross pay: only pay for days actually worked/partially worked
        $grossPay        = $dailyRate * $attendance['present'];
        $halfDayPay      = ($dailyRate / 2) * $attendance['half_day'];
        $lateDeduction   = ($dailyRate * 0.05) * $attendance['late'];

        $totalGross          = round($grossPay + $halfDayPay, 2);
        $attendanceDeduction = round($lateDeduction, 2);

        // Government contributions (pro-rated by period, only if enabled for the shop)
        $sssMonthly        = $this->calculateSSS($monthlySalary);
        $philhealthMonthly = $this->calculatePhilHealth($monthlySalary);
        $pagibigMonthly    = $this->calculatePagIbig($monthlySalary);

        $sss        = $shop->deduct_sss        ? round($sssMonthly * $periodFactor, 2)        : 0.0;
        $philhealth = $shop->deduct_philhealth  ? round($philhealthMonthly * $periodFactor, 2) : 0.0;
        $pagibig    = $shop->deduct_pagibig     ? round($pagibigMonthly * $periodFactor, 2)    : 0.0;

        // Withholding tax on taxable income for this period
        $taxableIncome  = max(0.0, $totalGross - $sss - $philhealth - $pagibig);
        $withholdingTax = $shop->deduct_withholding_tax
            ? $this->calculateWithholdingTax($taxableIncome)
            : 0.0;

        $netPay = max(0, round($totalGross - $attendanceDeduction - $sss - $philhealth - $pagibig - $withholdingTax, 2));

        return [
            'basic_salary'            => $monthlySalary,
            'days_worked'             => $attendance['present'],
            'days_absent'             => $attendance['absent'],
            'days_late'               => $attendance['late'],
            'days_half_day'           => $attendance['half_day'],
            'deductions'              => $attendanceDeduction,
            'sss_contribution'        => $sss,
            'philhealth_contribution' => $philhealth,
            'pagibig_contribution'    => $pagibig,
            'withholding_tax'         => $withholdingTax,
            'bonuses'                 => 0,
            'net_pay'                 => $netPay,
        ];
    }
}
