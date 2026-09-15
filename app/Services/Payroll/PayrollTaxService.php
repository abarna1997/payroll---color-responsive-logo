<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\TaxYear;
use App\Models\TaxRule;

class PayrollTaxService
{
    public function calculateTax(float $grossSalary, Employee $employee, PayrollPeriod $period): array
    {
        $periodDate = $period->end_date;

        // 1. Resolve applicable tax year
        $taxYear = TaxYear::where('status', 'APPROVED')
            ->where('start_date', '<=', $periodDate)
            ->where('end_date', '>=', $periodDate)
            ->first();

        if (!$taxYear) {
            return $this->emptyTaxResult($grossSalary);
        }

        // 2. Resolve rule version
        $taxRule = TaxRule::where('tax_year_id', $taxYear->id)
            ->where('status', 'APPROVED')
            ->where('effective_from', '<=', $periodDate)
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $periodDate);
            })
            ->with(['brackets', 'reliefs'])
            ->first();

        if (!$taxRule) {
            return $this->emptyTaxResult($grossSalary, $taxYear);
        }

        // 3. Resolve reliefs
        $totalRelief = 0.00;
        foreach ($taxRule->reliefs as $relief) {
            $totalRelief += $relief->amount;
        }

        // Branch based on Employee Tax Profile
        if ($employee->tax_cumulative_enabled) {
            return $this->calculateCumulativeTable05($grossSalary, $employee, $taxYear, $taxRule, $period, $totalRelief);
        }

        return $this->calculateStandardTable01($grossSalary, $taxYear, $taxRule, $totalRelief);
    }

    protected function calculateStandardTable01(float $grossSalary, TaxYear $taxYear, TaxRule $taxRule, float $totalRelief): array
    {
        $taxableIncome = max(0.00, $grossSalary - $totalRelief);

        if ($taxableIncome <= 0) {
            return $this->buildTaxResult($taxYear, $taxRule, 0.00, $totalRelief, 0.00, 'Table 01');
        }

        $taxAmount = $this->applyBrackets($taxableIncome, $taxRule->brackets);

        return $this->buildTaxResult($taxYear, $taxRule, $taxableIncome, $totalRelief, $taxAmount, 'Table 01');
    }

    protected function calculateCumulativeTable05(float $grossSalary, Employee $employee, TaxYear $taxYear, TaxRule $taxRule, PayrollPeriod $period, float $periodRelief): array
    {
        // YTD calculations
        $ytdGross = \App\Models\Payslip::where('employee_id', $employee->id)
            ->where('payroll_period_id', '!=', $period->id)
            ->whereHas('payrollPeriod', function($q) use ($taxYear) {
                $q->where('end_date', '>=', $taxYear->start_date)
                  ->where('end_date', '<=', $taxYear->end_date);
            })
            ->where('status', 'Locked')
            ->sum('gross_salary');

        $ytdTaxDeducted = \App\Models\Payslip::where('employee_id', $employee->id)
            ->where('payroll_period_id', '!=', $period->id)
            ->whereHas('payrollPeriod', function($q) use ($taxYear) {
                $q->where('end_date', '>=', $taxYear->start_date)
                  ->where('end_date', '<=', $taxYear->end_date);
            })
            ->where('status', 'Locked')
            ->sum('tax_amount');
            
        $ytdRelief = \App\Models\Payslip::where('employee_id', $employee->id)
            ->where('payroll_period_id', '!=', $period->id)
            ->whereHas('payrollPeriod', function($q) use ($taxYear) {
                $q->where('end_date', '>=', $taxYear->start_date)
                  ->where('end_date', '<=', $taxYear->end_date);
            })
            ->where('status', 'Locked')
            ->sum('tax_relief');

        $totalIncome = $ytdGross + $grossSalary;
        $totalCumulativeRelief = $ytdRelief + $periodRelief;

        $taxableIncome = max(0.00, $totalIncome - $totalCumulativeRelief);

        if ($taxableIncome <= 0) {
            return $this->buildTaxResult($taxYear, $taxRule, 0.00, $periodRelief, 0.00, 'Table 05 (Cumulative)');
        }

        $totalTax = $this->applyBrackets($taxableIncome, $taxRule->brackets);
        
        $taxAmount = max(0.00, $totalTax - $ytdTaxDeducted);

        return $this->buildTaxResult($taxYear, $taxRule, max(0.00, $grossSalary - $periodRelief), $periodRelief, $taxAmount, 'Table 05 (Cumulative)');
    }

    protected function applyBrackets(float $taxableIncome, $brackets): float
    {
        $taxAmount = 0.00;
        $remaining = $taxableIncome;

        foreach ($brackets as $bracket) {
            $slabSize = $bracket->maximum_amount ? ($bracket->maximum_amount - $bracket->minimum_amount) : PHP_FLOAT_MAX;
            
            $taxableInSlab = min($remaining, $slabSize);
            
            if ($taxableInSlab > 0) {
                $taxAmount += $taxableInSlab * ($bracket->rate / 100);
                $remaining -= $taxableInSlab;
            }

            if ($remaining <= 0) {
                break;
            }
        }
        
        return $taxAmount;
    }

    protected function buildTaxResult(TaxYear $taxYear, TaxRule $taxRule, float $taxableIncome, float $reliefApplied, float $taxAmount, string $calculationMethod = 'Table 01'): array
    {
        return [
            'tax_year_id' => $taxYear->id,
            'tax_rule_id' => $taxRule->id,
            'tax_rule_version' => $taxRule->version,
            'taxable_income' => round($taxableIncome, 2),
            'relief_applied' => round($reliefApplied, 2),
            'tax_amount' => round($taxAmount, 2),
            'calculation_method' => $calculationMethod,
        ];
    }

    protected function emptyTaxResult(float $grossSalary, ?TaxYear $taxYear = null): array
    {
        return [
            'tax_year_id' => $taxYear ? $taxYear->id : null,
            'tax_rule_id' => null,
            'tax_rule_version' => null,
            'taxable_income' => round($grossSalary, 2),
            'relief_applied' => 0.00,
            'tax_amount' => 0.00,
            'calculation_method' => 'None',
        ];
    }
}
