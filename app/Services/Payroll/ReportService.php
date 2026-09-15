<?php

namespace App\Services\Payroll;

use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Compile summary statistics for a collection of payslips in a period.
     */
    public function getSummary(Collection $payslips): array
    {
        return [
            'gross_sum' => round($payslips->sum('gross_salary'), 2),
            'net_sum' => round($payslips->sum('net_salary'), 2),
            'epf_employee_sum' => round($payslips->sum('epf_employee'), 2),
            'epf_employer_sum' => round($payslips->sum('epf_employer'), 2),
            'etf_employer_sum' => round($payslips->sum('etf_employer'), 2),
            'ot_sum' => round($payslips->sum('ot_payment'), 2),
            'no_pay_sum' => round($payslips->sum('no_pay_deduction'), 2),
            'apit_sum' => round($payslips->sum('apit'), 2),
            'total_deductions_sum' => round($payslips->sum(function ($s) {
                return (float)$s->no_pay_deduction + (float)$s->epf_employee + (float)$s->loan_deduction + (float)$s->advance_deduction + (float)$s->late_deduction + (float)$s->other_deductions;
            }), 2),
            'count' => $payslips->count(),
        ];
    }

    /**
     * Group payslips by department for reports.
     */
    public function groupByDepartment(Collection $payslips): Collection
    {
        return $payslips->groupBy(function ($slip) {
            return $slip->employee->department->name ?? 'Unassigned';
        });
    }

    /**
     * Group payslips by branch for reports.
     */
    public function groupByBranch(Collection $payslips): Collection
    {
        return $payslips->groupBy(function ($slip) {
            return $slip->employee->branch->name ?? 'Unassigned';
        });
    }
}
