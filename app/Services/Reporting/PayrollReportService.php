<?php

namespace App\Services\Reporting;

use App\Models\Payslip;
use Illuminate\Support\Facades\DB;

class PayrollReportService
{
    /**
     * Aggregate payroll costs including EPF, ETF, Taxes.
     */
    public function getPayrollCostSummary($companyId = null, $startDate = null, $endDate = null)
    {
        $query = Payslip::query();

        if ($companyId) {
            $query->whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereHas('period', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate]);
            });
        }

        return $query->select(
            DB::raw('SUM(basic_salary) as total_basic'),
            DB::raw('SUM(gross_salary) as total_gross'),
            DB::raw('SUM(net_salary) as total_net'),
            DB::raw('SUM(epf_employee) as total_epf_employee'),
            DB::raw('SUM(epf_employer) as total_epf_employer'),
            DB::raw('SUM(etf_employer) as total_etf_employer'),
            DB::raw('SUM(apit) as total_tax'),
            DB::raw('SUM(ot_amount) as total_ot')
        )->first();
    }
}
