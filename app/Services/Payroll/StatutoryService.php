<?php

namespace App\Services\Payroll;

use App\Models\Employee;

class StatutoryService
{
    /**
     * Calculate EPF & ETF contributions based on settings.
     */
    public function calculateContributions(Employee $employee, float $basicSalary, float $fixedAllowances): array
    {
        $profile = $employee->salaryProfile;
        if (!$profile) {
            return [
                'epf_employee' => 0.00,
                'epf_employer' => 0.00,
                'etf_employer' => 0.00
            ];
        }

        $enableEpf = payroll_setting('EnableEPF', true);
        $enableEtf = payroll_setting('EnableETF', true);

        $epfEmployeeRate = (float) payroll_setting('EPFEmployeeRate', 8.00) / 100;
        $epfEmployerRate = (float) payroll_setting('EPFEmployerRate', 12.00) / 100;
        $etfEmployerRate = (float) payroll_setting('ETFRate', 3.00) / 100;

        // Sri Lankan Statutory Compliances Base = Basic Salary + Fixed Allowances
        $epfBase = $basicSalary + $fixedAllowances;

        $epfEmployee = ($enableEpf && $profile->epf_eligible) ? ($epfBase * $epfEmployeeRate) : 0.00;
        $epfEmployer = ($enableEpf && $profile->epf_eligible) ? ($epfBase * $epfEmployerRate) : 0.00;
        $etfEmployer = ($enableEtf && $profile->etf_eligible) ? ($epfBase * $etfEmployerRate) : 0.00;

        return [
            'epf_employee' => round($epfEmployee, 2),
            'epf_employer' => round($epfEmployer, 2),
            'etf_employer' => round($etfEmployer, 2)
        ];
    }
}
