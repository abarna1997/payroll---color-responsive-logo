<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PayrollCalculationService
{
    protected $validator;
    protected $attendanceService;
    protected $overtimeService;
    protected $statutoryService;
    protected $apitService;
    protected $loanService;
    protected $allowanceService;
    protected $deductionService;
    protected $payslipService;
    protected $auditService;
    protected $payrollAttendanceService;
    protected $payrollConfigurationService;
    protected $payrollOTService;
    protected $payrollTaxService;
    protected $payrollSalaryProfileService;

    public function __construct(
        ValidationService $validator,
        AttendanceService $attendanceService,
        OvertimeService $overtimeService,
        StatutoryService $statutoryService,
        APITService $apitService,
        LoanService $loanService,
        AllowanceService $allowanceService,
        DeductionService $deductionService,
        PayslipService $payslipService,
        AuditService $auditService,
        PayrollAttendanceService $payrollAttendanceService,
        PayrollConfigurationService $payrollConfigurationService,
        PayrollOTService $payrollOTService,
        PayrollTaxService $payrollTaxService,
        PayrollSalaryProfileService $payrollSalaryProfileService = null
    ) {
        $this->validator = $validator;
        $this->attendanceService = $attendanceService;
        $this->overtimeService = $overtimeService;
        $this->statutoryService = $statutoryService;
        $this->apitService = $apitService;
        $this->loanService = $loanService;
        $this->allowanceService = $allowanceService;
        $this->deductionService = $deductionService;
        $this->payslipService = $payslipService;
        $this->auditService = $auditService;
        $this->payrollAttendanceService = $payrollAttendanceService;
        $this->payrollConfigurationService = $payrollConfigurationService;
        $this->payrollOTService = $payrollOTService;
        $this->payrollTaxService = $payrollTaxService;
        $this->payrollSalaryProfileService = $payrollSalaryProfileService ?: app(PayrollSalaryProfileService::class);
    }

    /**
     * Compute and save/update the payslip for a single employee in a given period.
     *
     * @return array Array of validation errors if validation fails, empty array if calculation succeeded.
     */
    public function calculate(PayrollPeriod $period, Employee $employee, array $overrides = [], ?int $payrollRunId = null): array
    {
        if (in_array($period->status, ['Locked', 'Approved', 'HR Review'])) {
            throw new \Exception("Cannot recalculate payroll for a {$period->status} period.");
        }

        // 1. Run Validation
        $errors = $this->validator->validate($period, $employee);
        if (count($errors) > 0) {
            return $errors;
        }

        $profile = $this->payrollSalaryProfileService->resolveForPeriod($employee, $period);
        
        // Use the new centralized PayrollAttendanceService
        try {
            $stats = $this->payrollAttendanceService->getAttendanceForPeriod($employee, $period);
        } catch (\Exception $e) {
            return [$e->getMessage()];
        }

        $basic = (float) ($profile ? $profile->basic_salary : 0.00);

        // Inputs for Sri Lankan formula
        // NoPayDays is driven directly from the centralized status
        $noWfhDays = isset($overrides['no_wfh_days']) ? (float) $overrides['no_wfh_days'] : (float) ($stats['v_days'] ?? 0);
        $halfDays = isset($overrides['half_days']) ? (float) $overrides['half_days'] : (float) ($stats['h_days'] ?? 0);
        
        // Map remaining fields for possible usage
        $lateMinutes = (float) ($stats['late_minutes'] ?? 0);
        $earlyOutMinutes = (float) ($stats['early_out_minutes'] ?? 0);
        $overtimeMinutes = (float) ($stats['overtime_minutes'] ?? 0);
        // Look up imported KPI System score for employee and period if not overridden
        $periodDate = \Illuminate\Support\Carbon::parse($period->start_date);
        $periodMonth = (int) $periodDate->format('m');
        $periodYear = (int) $periodDate->format('Y');

        $kpiScoreRecord = \App\Models\EmployeeKpiScore::where(function ($query) use ($employee) {
            $query->where('local_employee_id', $employee->id);
            if (! empty($employee->employee_id)) {
                $query->orWhere('employee_id', (int) $employee->employee_id);
            }
            if (! empty($employee->email)) {
                $query->orWhere('email', $employee->email);
            }
            if (! empty($employee->company_email)) {
                $query->orWhere('email', $employee->company_email);
            }
        })
        ->where('month', $periodMonth)
        ->where('year', $periodYear)
        ->first();

        if (isset($overrides['kpi_percentage'])) {
            $kpiPercent = (float) $overrides['kpi_percentage'];
        } elseif ($kpiScoreRecord && $kpiScoreRecord->final_kpi_score !== null) {
            $kpiPercent = (float) $kpiScoreRecord->final_kpi_score;
        } else {
            $kpiPercent = 100.00;
        }

        $incentive = isset($overrides['incentive']) ? (float) $overrides['incentive'] : (float) ($profile ? $profile->incentive : 0.00);
        $profileDeductions = $profile && is_array($profile->deductions_json) ? collect($profile->deductions_json) : collect([]);
        
        $advanceItem = $profileDeductions->firstWhere('name', 'Advance');
        $advance = isset($overrides['advance_deduction']) ? (float) $overrides['advance_deduction'] : (float) (is_array($advanceItem) ? ($advanceItem['amount'] ?? 0) : 0);
        
        $loanItem = $profileDeductions->firstWhere('name', 'Loan');
        $loan = isset($overrides['loan_deduction']) ? (float) $overrides['loan_deduction'] : (float) (is_array($loanItem) ? ($loanItem['amount'] ?? 0) : 0);
        
        $apitItem = $profileDeductions->firstWhere('name', 'APIT');
        $apit = isset($overrides['apit']) ? (float) $overrides['apit'] : (float) (is_array($apitItem) ? ($apitItem['amount'] ?? 0) : 0);

        if ($apit == 0) {
            $apit = $this->apitService->calculateTax($basic + $incentive);
        }

        $totalPackage = isset($overrides['total_package']) ? (float) $overrides['total_package'] : ($basic + $incentive);

        // Exact Sri Lankan Payroll Formulas:
        // NoPayDays   = V*1 + H*0.5
        // NoPayAmount = (Basic / 30) * NoPayDays
        // Gross       = Basic - NoPayAmount + Approved OT
        // ...

        $noPayDivisor = (float) $this->payrollConfigurationService->getValue('no_pay_divisor', $period->end_date, 30.0, $employee->company_id);
        $epfEmployeeRate = (float) $this->payrollConfigurationService->getValue('epf_employee_rate', $period->end_date, 8.0, $employee->company_id) / 100;
        $epfEmployerRate = (float) $this->payrollConfigurationService->getValue('epf_employer_rate', $period->end_date, 12.0, $employee->company_id) / 100;
        $etfRate = (float) $this->payrollConfigurationService->getValue('etf_rate', $period->end_date, 3.0, $employee->company_id) / 100;

        $noPayDays = round(($noWfhDays * 1.0) + ($halfDays * 0.5), 2);
        
        // Prevent division by zero
        $noPayDivisorSafe = $noPayDivisor > 0 ? $noPayDivisor : 30.0;
        $noPayAmount = round(($basic / $noPayDivisorSafe) * $noPayDays, 2);
        
        // Calculate OT monetarily via framework
        $otResult = $this->payrollOTService->calculateOT($stats, $employee, $period, $basic);
        $otAmount = $otResult['ot_amount'];

        // --- EPF BASIS ---
        // Preserving the exact previous EPF basis (Basic + OT - NoPay). Do NOT change.
        $epfLiableEarnings = round(max(0.00, $basic - $noPayAmount + $otAmount), 2);

        $epfEmployee = round($epfLiableEarnings * $epfEmployeeRate, 2);
        $epfEmployer = round($epfLiableEarnings * $epfEmployerRate, 2);
        $etfEmployer = round($epfLiableEarnings * $etfRate, 2);
        $epfTotal = round($epfEmployee + $epfEmployer, 2);

        $deduInc = round(($incentive / $noPayDivisorSafe) * $noPayDays, 2);
        // If KPI < 60, total incentive is forfeited, so non-performance deduction covers remaining incentive
        $deduIncNp = ($kpiPercent < 60.0) ? max(0.00, round($incentive - $deduInc, 2)) : 0.00;

        $companyId = $employee->company_id;
        $periodEndDate = \Illuminate\Support\Carbon::parse($period->end_date);

        // Resolve Dynamic Salary Components
        $assignedComponents = \App\Models\EmployeeSalaryComponent::with('salaryComponent')
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where('effective_from', '<=', $periodEndDate)
            ->where(function ($q) use ($periodEndDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $periodEndDate);
            })
            ->get();

        $dynamicAllowances = [];
        $dynamicDeductions = [];

        foreach ($assignedComponents as $assignment) {
            $component = $assignment->salaryComponent;
            if (!$component || !$component->is_active) {
                continue;
            }

            $value = $assignment->value ?? $component->default_value ?? 0.00;
            $value = (float) $value;

            if ($component->type === 'Allowance') {
                $dynamicAllowances[$component->name] = ['name' => $component->name, 'amount' => $value];
            } else {
                $dynamicDeductions[$component->name] = ['name' => $component->name, 'amount' => $value];
            }
        }

        // Resolve Fixed Profile Components
        $profileAllowances = [];
        if ($profile && is_array($profile->allowances_json)) {
            foreach ($profile->allowances_json as $item) {
                $profileAllowances[$item['name']] = ['name' => $item['name'], 'amount' => (float)($item['amount'] ?? 0)];
            }
        }

        $profileDeductionsArr = [];
        if ($profile && is_array($profile->deductions_json)) {
            foreach ($profile->deductions_json as $item) {
                $profileDeductionsArr[$item['name']] = ['name' => $item['name'], 'amount' => (float)($item['amount'] ?? 0)];
            }
        }

        // Merge components (Dynamic overrides Fixed to prevent duplication)
        $finalAllowances = array_merge($profileAllowances, $dynamicAllowances);
        $finalDeductions = array_merge($profileDeductionsArr, $dynamicDeductions);

        // Convert back to sequential arrays for JSON storage
        $finalAllowancesList = array_values($finalAllowances);
        $finalDeductionsList = array_values($finalDeductions);

        // Ensure overridden APIT, Advance, Loan are correctly set in the final deductions list
        foreach (['APIT' => $apit, 'Advance' => $advance, 'Loan' => $loan] as $name => $amount) {
            if ($amount > 0) {
                $found = false;
                foreach ($finalDeductionsList as &$item) {
                    if ($item['name'] === $name) {
                        $item['amount'] = $amount;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $finalDeductionsList[] = ['name' => $name, 'amount' => $amount];
                }
            }
        }

        $totalAllowancesSum = collect($finalAllowancesList)->sum('amount');
        $totalDeductionsSum = collect($finalDeductionsList)->sum('amount');

        // GROSS SALARY = Basic - NoPay + OT + Incentive + All Allowances
        $gross = round(max(0.00, $basic - $noPayAmount + $otAmount) + $incentive + $totalAllowancesSum, 2);

        // NET SALARY = Gross - EPF - Total Deductions - DeduInc - DeduIncNp
        $net = round($gross - $epfEmployee - $totalDeductionsSum - $deduInc - $deduIncNp, 2);

        // SHADOW APIT Calculation
        $shadowTaxResult = $this->payrollTaxService->calculateTax($basic + $incentive + $totalAllowancesSum, $employee, $period);

        // Metadata fields
        $currency = payroll_setting('Currency', 'LKR');
        $exchangeRate = (float) payroll_setting('ExchangeRate', 1.0000);
        $paymentMethod = payroll_setting('PaymentMethod', 'Bank Transfer');
        $bankAccount = $employee->bank_account;
        $refNumber = $this->payslipService->generateReferenceNumber($employee, $period);
        $hash = Str::random(32);

        // Save/Update Payslip
        Payslip::updateOrCreate(
            [
                'payroll_period_id' => $period->id,
                'employee_id' => $employee->id,
            ],
            [
                'payroll_run_id' => $payrollRunId,
                'basic_salary' => $basic,
                'total_package' => $totalPackage,
                'no_wfh_days' => $noWfhDays,
                'half_days' => $halfDays,
                'no_pay_days' => $noPayDays,
                'no_pay_deduction' => $noPayAmount,
                'no_pay_divisor' => $noPayDivisor,
                'ot_payment' => $otAmount, // from OT calculation
                'ot_amount' => $otAmount,
                'ot_minutes_calculated' => $otResult['ot_minutes_calculated'],
                'ot_hours_calculated' => $otResult['ot_hours_calculated'],
                'ot_formula_version' => $otResult['ot_formula_version'],
                'ot_formula_type' => $otResult['ot_formula_type'],
                'ot_rate_applied' => $otResult['ot_rate_applied'],
                'ot_multiplier_applied' => $otResult['ot_multiplier_applied'],
                'gross_salary' => $gross,
                'incentive' => $incentive,
                'dedu_inc' => $deduInc,
                'kpi_percentage' => $kpiPercent,
                'dedu_inc_np' => $deduIncNp,
                'advance_deduction' => $advance,
                'loan_deduction' => $loan,
                'apit' => $apit, // keeping production apit active
                'tax_year_id' => $shadowTaxResult['tax_year_id'],
                'tax_rule_id' => $shadowTaxResult['tax_rule_id'],
                'taxable_income' => $shadowTaxResult['taxable_income'],
                'relief_applied' => $shadowTaxResult['relief_applied'],
                'tax_amount' => $shadowTaxResult['tax_amount'],
                'epf_employee' => $epfEmployee,
                'epf_employer' => $epfEmployer,
                'etf_employer' => $etfEmployer,
                'epf_employee_rate' => $epfEmployeeRate * 100,
                'epf_employer_rate' => $epfEmployerRate * 100,
                'etf_employer_rate' => $etfRate * 100,
                'epf_total' => $epfTotal,
                'net_salary' => $net,

                'allowances_json' => $finalAllowancesList,
                'deductions_json' => $finalDeductionsList,
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'payment_method' => $paymentMethod,
                'bank_account' => $bankAccount,
                'reference_number' => $refNumber,
                'verification_hash' => $hash,
            ]
        );

        return [];
    }
}
