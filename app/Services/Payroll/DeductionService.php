<?php

namespace App\Services\Payroll;

use App\Models\Employee;

class DeductionService
{
    /**
     * Calculate deductions for an employee (excluding loans/statutory/no-pay).
     */
    public function calculateDeductions(Employee $employee, array $attendanceStats): array
    {
        // For late penalties: check if late grace is enabled or if there are deductions associated.
        // As a baseline, late penalties can be a flat deduction per late day, or hourly.
        // Let's implement a calculation: (Basic Salary / SalaryDaysPerMonth / 8) * late_hours * LatePenaltyRate
        $lateDeduction = 0.00;
        $lateDays = (int) ($attendanceStats['late_days'] ?? 0);
        
        $latePenaltyRate = (float) payroll_setting('LatePenaltyRate', 0.00); // Default to 0 unless set
        if ($latePenaltyRate > 0) {
            $lateDeduction = $lateDays * $latePenaltyRate;
        }

        return [
            'late_deduction' => round($lateDeduction, 2),
            'other_deductions' => 0.00
        ];
    }
}
