<?php

namespace App\Services\Payroll;

use App\Models\Employee;

class LoanService
{
    /**
     * Calculate loan and advance deductions for an employee.
     */
    public function calculateDeductions(Employee $employee): array
    {
        $profile = $employee->salaryProfile;
        if (!$profile) {
            return [
                'loan_deduction' => 0.00,
                'advance_deduction' => 0.00,
                'fixed_deductions_sum' => 0.00
            ];
        }

        $enableLoan = payroll_setting('LoanAutoDeduction', true);
        $enableAdvance = payroll_setting('AdvanceSalaryDeduction', true);

        $loanVal = 0.00;
        $advanceVal = 0.00;
        $otherVal = 0.00;

        if (is_array($profile->deductions_json)) {
            foreach ($profile->deductions_json as $ded) {
                $name = strtolower($ded['name'] ?? '');
                $amount = (float) ($ded['amount'] ?? 0);

                if ($name === 'loan' || str_contains($name, 'loan')) {
                    if ($enableLoan) {
                        $loanVal += $amount;
                    }
                } elseif ($name === 'advance' || str_contains($name, 'advance') || str_contains($name, 'adv')) {
                    if ($enableAdvance) {
                        $advanceVal += $amount;
                    }
                } else {
                    $otherVal += $amount;
                }
            }
        }

        return [
            'loan_deduction' => round($loanVal, 2),
            'advance_deduction' => round($advanceVal, 2),
            'other_deductions' => round($otherVal, 2),
            'fixed_deductions_sum' => round($loanVal + $advanceVal + $otherVal, 2)
        ];
    }
}
