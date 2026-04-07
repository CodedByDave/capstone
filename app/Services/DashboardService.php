<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getKPIs(Shop $shop): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Employee metrics
        $totalEmployees = Employee::where('shop_id', $shop->id)->where('status', 'Active')->count();
        $lastMonthEmployees = Employee::where('shop_id', $shop->id)
            ->where('status', 'Active')
            ->where('created_at', '<=', $lastMonthEnd)
            ->count();

        // Attendance rate this month
        $totalAttendance = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();
        $presentCount = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        // Last month attendance rate
        $lastTotalAtt = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $lastPresentCount = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->whereIn('status', ['present', 'late'])
            ->count();
        $lastAttendanceRate = $lastTotalAtt > 0 ? round(($lastPresentCount / $lastTotalAtt) * 100, 1) : 0;

        // Absenteeism rate
        $absentCount = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'absent')
            ->count();
        $absenteeismRate = $totalAttendance > 0 ? round(($absentCount / $totalAttendance) * 100, 1) : 0;

        // Late rate
        $lateCount = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'late')
            ->count();
        $lateRate = $totalAttendance > 0 ? round(($lateCount / $totalAttendance) * 100, 1) : 0;

        // Payroll cost this month
        $currentPayroll = Payroll::where('shop_id', $shop->id)
            ->where('period_start', '>=', $startOfMonth)
            ->where('period_end', '<=', $endOfMonth)
            ->pluck('id');
        $payrollCost = PayrollItem::whereIn('payroll_id', $currentPayroll)->sum('net_pay');

        $lastPayrollIds = Payroll::where('shop_id', $shop->id)
            ->where('period_start', '>=', $lastMonthStart)
            ->where('period_end', '<=', $lastMonthEnd)
            ->pluck('id');
        $lastPayrollCost = PayrollItem::whereIn('payroll_id', $lastPayrollIds)->sum('net_pay');

        // Cost per employee
        $costPerEmployee = $totalEmployees > 0 ? round($payrollCost / $totalEmployees, 2) : 0;

        // Overtime/half-day ratio
        $halfDayCount = Attendance::where('shop_id', $shop->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'half_day')
            ->count();

        return [
            'total_employees' => $totalEmployees,
            'employee_change' => $totalEmployees - $lastMonthEmployees,
            'attendance_rate' => $attendanceRate,
            'attendance_change' => round($attendanceRate - $lastAttendanceRate, 1),
            'absenteeism_rate' => $absenteeismRate,
            'late_rate' => $lateRate,
            'payroll_cost' => round($payrollCost, 2),
            'payroll_change' => $lastPayrollCost > 0 ? round((($payrollCost - $lastPayrollCost) / $lastPayrollCost) * 100, 1) : 0,
            'cost_per_employee' => $costPerEmployee,
            'half_day_count' => $halfDayCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'present_count' => $presentCount,
        ];
    }

    public function getAttendanceTrend(Shop $shop, int $days = 14): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $records = Attendance::where('shop_id', $shop->id)
            ->where('date', '>=', $startDate)
            ->selectRaw("date, status, COUNT(*) as count")
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $trend = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $dayRecords = $records->where('date', $date);
            $trend[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format('M d'),
                'present' => $dayRecords->where('status', 'present')->sum('count'),
                'absent' => $dayRecords->where('status', 'absent')->sum('count'),
                'late' => $dayRecords->where('status', 'late')->sum('count'),
                'half_day' => $dayRecords->where('status', 'half_day')->sum('count'),
            ];
        }

        return $trend;
    }

    public function getPayrollTrend(Shop $shop, int $limit = 6): array
    {
        $payrolls = Payroll::where('shop_id', $shop->id)
            ->where('status', 'finalized')
            ->latest('period_start')
            ->take($limit)
            ->get()
            ->reverse()
            ->values();

        return $payrolls->map(function ($p) {
            $totalNet = $p->items()->sum('net_pay');
            $totalDeductions = $p->items()->sum('deductions');
            $totalBonuses = $p->items()->sum('bonuses');
            $employeeCount = $p->items()->count();

            return [
                'label' => $p->period_label,
                'net_pay' => round($totalNet, 2),
                'deductions' => round($totalDeductions, 2),
                'bonuses' => round($totalBonuses, 2),
                'employees' => $employeeCount,
            ];
        })->toArray();
    }

    public function getEmployeePerformance(Shop $shop): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $employees = Employee::where('shop_id', $shop->id)
            ->where('status', 'Active')
            ->get(['id', 'first_name', 'last_name', 'position', 'branch_name']);

        return $employees->map(function ($emp) use ($startOfMonth, $endOfMonth) {
            $attendance = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth]);

            $total = (clone $attendance)->count();
            $present = (clone $attendance)->where('status', 'present')->count();
            $absent = (clone $attendance)->where('status', 'absent')->count();
            $late = (clone $attendance)->where('status', 'late')->count();

            return [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'position' => $emp->position,
                'branch' => $emp->branch_name,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        })
        ->sortBy('rate')
        ->values()
        ->toArray();
    }

    public function getInsights(array $kpis): array
    {
        $insights = [];

        if ($kpis['attendance_rate'] < 80) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Low Attendance Rate',
                'message' => "Attendance is at {$kpis['attendance_rate']}% this month. Consider reviewing schedules or addressing recurring absences. Target: 90%+.",
                'action' => 'Review attendance records and speak with frequently absent employees.',
            ];
        } elseif ($kpis['attendance_rate'] >= 95) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Excellent Attendance',
                'message' => "Attendance rate is {$kpis['attendance_rate']}%. Your team is showing strong commitment.",
                'action' => 'Consider recognizing employees with perfect attendance.',
            ];
        }

        if ($kpis['late_rate'] > 15) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'High Tardiness Rate',
                'message' => "{$kpis['late_rate']}% of attendance records show tardiness. This may indicate scheduling issues or low morale.",
                'action' => 'Review shift start times and consider adjusting schedules.',
            ];
        }

        if ($kpis['payroll_change'] > 20) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Payroll Cost Increase',
                'message' => "Payroll costs increased by {$kpis['payroll_change']}% compared to last month.",
                'action' => 'Check if the increase is due to new hires, overtime, or bonuses.',
            ];
        } elseif ($kpis['payroll_change'] < -10) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Payroll Cost Decrease',
                'message' => "Payroll costs decreased by " . abs($kpis['payroll_change']) . "% compared to last month.",
                'action' => 'Verify this is due to efficiency gains and not understaffing.',
            ];
        }

        if ($kpis['absenteeism_rate'] > 10) {
            $insights[] = [
                'type' => 'danger',
                'title' => 'High Absenteeism',
                'message' => "{$kpis['absenteeism_rate']}% absenteeism rate could impact service quality and increase workload on present staff.",
                'action' => 'Investigate root causes — consider one-on-one meetings with frequently absent staff.',
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'type' => 'success',
                'title' => 'All Metrics Look Good',
                'message' => 'Your shop metrics are within healthy ranges. Keep up the good work!',
                'action' => 'Continue monitoring and maintain current management practices.',
            ];
        }

        return $insights;
    }
}
