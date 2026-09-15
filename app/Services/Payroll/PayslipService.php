<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;

class PayslipService
{
    /**
     * Generate unique verification hash for a computed payslip.
     */
    public function generateHash(Employee $employee, PayrollPeriod $period, float $netSalary): string
    {
        return hash('sha256', $employee->id . '|' . $period->id . '|' . $netSalary . '|' . time());
    }

    /**
     * Generate unique verification ID (Reference number format: AMS-YYYYMM-XXXXX).
     */
    public function generateReferenceNumber(Employee $employee, PayrollPeriod $period): string
    {
        $yearMonth = $period->start_date->format('Ym');
        $paddedId = str_pad($employee->id, 5, '0', STR_PAD_LEFT);
        return "AMS-{$yearMonth}-{$paddedId}";
    }

    /**
     * Get digital verification URL.
     */
    public function getVerificationUrl(Payslip $payslip): string
    {
        return url("/payroll/payslip/{$payslip->id}");
    }
}
