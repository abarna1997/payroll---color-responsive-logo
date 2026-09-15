<?php

namespace App\Services\Payroll;

use App\Models\Employee;

class AllowanceService
{
    /**
     * Calculate allowances for an employee.
     */
    public function calculateAllowances(Employee $employee, array $attendanceStats): array
    {
        $profile = $employee->salaryProfile;
        if (!$profile) {
            return [
                'fixed_allowances' => 0.00,
                'variable_allowances' => 0.00,
                'attendance_bonus' => 0.00,
                'performance_bonus' => 0.00,
                'commission' => 0.00,
                'shift_allowance' => 0.00,
                'total_allowances' => 0.00
            ];
        }

        $fixedVal = 0.00;
        $varVal = 0.00;
        $perfVal = 0.00;
        $commissionVal = 0.00;
        $shiftVal = 0.00;

        if (is_array($profile->allowances_json)) {
            foreach ($profile->allowances_json as $allow) {
                $name = strtolower($allow['name'] ?? '');
                $amount = (float) ($allow['amount'] ?? 0);

                if (str_contains($name, 'kpi') || str_contains($name, 'perf')) {
                    $perfVal += $amount;
                } elseif (str_contains($name, 'commission') || str_contains($name, 'sales')) {
                    $commissionVal += $amount;
                } elseif (str_contains($name, 'shift') || str_contains($name, 'night')) {
                    $shiftVal += $amount;
                } elseif (str_contains($name, 'var')) {
                    $varVal += $amount;
                } else {
                    $fixedVal += $amount;
                }
            }
        }

        // Attendance Bonus (Configurable)
        $attBonus = 0.00;
        $enableAttBonus = payroll_setting('AttendanceBonusEnabled', false);
        if ($enableAttBonus) {
            // Check if employee has zero unpaid leaves and zero late arrivals
            $unpaidLeaves = (int) ($attendanceStats['unpaid_leaves'] ?? 0);
            $lateDays = (int) ($attendanceStats['late_days'] ?? 0);

            if ($unpaidLeaves === 0 && $lateDays === 0) {
                $attBonus = (float) payroll_setting('AttendanceBonusValue', 10000.00);
            }
        }

        $totalAllowances = $fixedVal + $varVal + $perfVal + $commissionVal + $shiftVal + $attBonus;

        return [
            'fixed_allowances' => round($fixedVal, 2),
            'variable_allowances' => round($varVal, 2),
            'attendance_bonus' => round($attBonus, 2),
            'performance_bonus' => round($perfVal, 2),
            'commission' => round($commissionVal, 2),
            'shift_allowance' => round($shiftVal, 2),
            'total_allowances' => round($totalAllowances, 2)
        ];
    }
}
