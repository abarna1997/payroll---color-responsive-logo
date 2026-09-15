<?php

namespace App\Services\Payroll;

use App\Models\AuditLog;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

class PayrollPeriodService
{
    /**
     * Create a new payroll period.
     */
    public function createPeriod(array $data, string $ipAddress): PayrollPeriod
    {
        $period = PayrollPeriod::create([
            'period_name' => $data['period_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'cycle_type' => $data['cycle_type'],
            'status' => 'Draft',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_PAYROLL_PERIOD',
            'module' => 'Payroll',
            'record_id' => $period->id,
            'new_value' => json_encode($period),
            'ip_address' => $ipAddress,
        ]);

        return $period;
    }

    /**
     * Get aggregate metrics for a payroll period.
     */
    public function getMetrics(PayrollPeriod $period): array
    {
        $payslips = Payslip::where('payroll_period_id', $period->id)->get();
        
        $epfEmp = $payslips->sum('epf_employee');
        $epfEmpr = $payslips->sum('epf_employer');

        return [
            'period_name' => $period->period_name,
            'employees_included' => $payslips->count(),
            'payroll_cost' => $payslips->sum('net_salary'),
            'total_epf' => $epfEmp + $epfEmpr,
            'total_etf' => $payslips->sum('etf_employer'),
            'total_ot' => $payslips->sum('ot_amount'),
        ];
    }
}
