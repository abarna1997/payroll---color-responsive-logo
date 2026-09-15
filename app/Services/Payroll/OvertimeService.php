<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Holiday;
use Carbon\Carbon;

class OvertimeService
{
    /**
     * Calculate Overtime payment details for an employee in a given period.
     */
    public function calculateOvertime(PayrollPeriod $period, Employee $employee, float $basicSalary): array
    {
        $enableOt = payroll_setting('EnableOvertime', true);
        if (!$enableOt) {
            return [
                'total_ot_payment' => 0.00,
                'normal_ot' => 0.00,
                'weekend_ot' => 0.00,
                'holiday_ot' => 0.00,
                'night_shift_ot' => 0.00
            ];
        }

        $otRateMultiplier = (float) payroll_setting('OvertimeMultiplier', 1.5);
        $weekendOTMultiplier = (float) payroll_setting('WeekendOTMultiplier', 1.5);
        $holidayOTMultiplier = (float) payroll_setting('HolidayOTMultiplier', 2.0);
        $poyaOTMultiplier = (float) payroll_setting('PoyaOTMultiplier', 1.5);
        $normalWorkingHours = (float) payroll_setting('WorkingHoursPerMonth', 240);

        if ($normalWorkingHours <= 0) {
            $normalWorkingHours = 240;
        }

        $holidays = Holiday::where('company_id', $employee->company_id)
            ->whereBetween('holiday_date', [$period->start_date->toDateString(), $period->end_date->toDateString()])
            ->where('affects_payroll', true)
            ->get();

        $logs = $employee->attendanceLogs()
            ->whereBetween('attendance_date', [$period->start_date->toDateString(), $period->end_date->toDateString()])
            ->get();

        $totalPayment = 0.00;
        $normalOtVal = 0.00;
        $weekendOtVal = 0.00;
        $holidayOtVal = 0.00;
        $nightShiftOtVal = 0.00; // Future extension

        foreach ($logs as $log) {
            if ($log->attendance_status === 'Overtime' && $log->attendance_type === 'Check-Out') {
                $shift = $employee->getMatchingShift($log->attendance_time);
                if ($shift) {
                    $shiftEnd = Carbon::parse($shift->end_time);
                    $punchTime = Carbon::parse($log->attendance_time);
                    $diffHours = max(0, $punchTime->diffInMinutes($shiftEnd) / 60.0);

                    $logDate = Carbon::parse($log->attendance_date);
                    $matchedHoliday = $holidays->first(fn($h) => Carbon::parse($h->holiday_date)->isSameDay($logDate));

                    if ($matchedHoliday) {
                        $multiplier = $matchedHoliday->holiday_type === 'Poya' ? $poyaOTMultiplier : $holidayOTMultiplier;
                        $otRate = ($basicSalary / $normalWorkingHours) * $multiplier;
                        $payment = $diffHours * $otRate;
                        $holidayOtVal += $payment;
                        $totalPayment += $payment;
                    } elseif ($logDate->isWeekend()) {
                        $otRate = ($basicSalary / $normalWorkingHours) * $weekendOTMultiplier;
                        $payment = $diffHours * $otRate;
                        $weekendOtVal += $payment;
                        $totalPayment += $payment;
                    } else {
                        $otRate = ($basicSalary / $normalWorkingHours) * $otRateMultiplier;
                        $payment = $diffHours * $otRate;
                        $normalOtVal += $payment;
                        $totalPayment += $payment;
                    }
                }
            }
        }

        return [
            'total_ot_payment' => round($totalPayment, 2),
            'normal_ot' => round($normalOtVal, 2),
            'weekend_ot' => round($weekendOtVal, 2),
            'holiday_ot' => round($holidayOtVal, 2),
            'night_shift_ot' => round($nightShiftOtVal, 2)
        ];
    }
}
