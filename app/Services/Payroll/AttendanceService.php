<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Calculate No-Pay Deduction for an employee in a given period.
     */
    public function calculateNoPay(PayrollPeriod $period, Employee $employee, array $attendanceStats, float $basicSalary): array
    {
        $enableNoPay = payroll_setting('EnableNoPay', true);
        if (!$enableNoPay) {
            return [
                'no_pay_days' => 0,
                'deduction' => 0.00
            ];
        }

        $unpaidLeaves = (int) ($attendanceStats['unpaid_leaves'] ?? 0);
        $overlapUnpaidHolidaysCount = 0;

        // Fetch company holidays affecting payroll
        $holidays = Holiday::where('company_id', $employee->company_id)
            ->whereBetween('holiday_date', [$period->start_date->toDateString(), $period->end_date->toDateString()])
            ->where('affects_payroll', true)
            ->get();

        // Get leave requests that fall in this period
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->where(function ($query) use ($period) {
                $query->whereBetween('start_date', [$period->start_date->toDateString(), $period->end_date->toDateString()])
                    ->orWhereBetween('end_date', [$period->start_date->toDateString(), $period->end_date->toDateString()]);
            })
            ->get();

        foreach ($leaveRequests as $req) {
            $typeName = strtolower($req->leaveType->name ?? '');
            $isUnpaid = str_contains($typeName, 'unpaid') || str_contains($typeName, 'no-pay') || str_contains($typeName, 'no pay');
            if ($isUnpaid) {
                $reqStart = Carbon::parse($req->start_date);
                $reqEnd = Carbon::parse($req->end_date);
                $overlapStart = $reqStart->greaterThan($period->start_date) ? $reqStart : $period->start_date;
                $overlapEnd = $reqEnd->lessThan($period->end_date) ? $reqEnd : $period->end_date;

                foreach ($holidays as $h) {
                    $hDate = Carbon::parse($h->holiday_date);
                    if ($hDate->between($overlapStart, $overlapEnd)) {
                        $overlapUnpaidHolidaysCount++;
                    }
                }
            }
        }

        $adjustedUnpaidLeaves = max(0, $unpaidLeaves - $overlapUnpaidHolidaysCount);

        // Settings-driven divisor (default 30)
        $noPayDivisor = (float) payroll_setting('SalaryDaysPerMonth', 30);
        if ($noPayDivisor <= 0) {
            $noPayDivisor = 30;
        }

        // Calculate No-Pay Deduction based on configurable formula or setting
        $noPayDeduction = ($basicSalary / $noPayDivisor) * $adjustedUnpaidLeaves;

        return [
            'no_pay_days' => $adjustedUnpaidLeaves,
            'deduction' => round($noPayDeduction, 2)
        ];
    }
}
