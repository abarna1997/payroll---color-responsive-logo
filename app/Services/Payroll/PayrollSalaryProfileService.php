<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalaryProfile;

class PayrollSalaryProfileService
{
    /**
     * Resolve the active SalaryProfile for an employee during a specific PayrollPeriod.
     * Rule:
     * - effective_from <= period_end
     * - AND (effective_to IS NULL OR effective_to >= period_start)
     * - AND status = 'Active'
     * 
     * If multiple exist, gets the most recent effective_from that matches.
     */
    public function resolveForPeriod(Employee $employee, PayrollPeriod $period): ?SalaryProfile
    {
        return SalaryProfile::where('employee_id', $employee->id)
            ->where('status', 'Active')
            ->where('effective_from', '<=', $period->end_date)
            ->where(function ($query) use ($period) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $period->start_date);
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}
