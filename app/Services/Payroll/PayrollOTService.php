<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;

class PayrollOTService
{
    protected $payrollConfigurationService;

    public function __construct(PayrollConfigurationService $payrollConfigurationService)
    {
        $this->payrollConfigurationService = $payrollConfigurationService;
    }

    public function calculateOT(array $attendanceStats, Employee $employee, PayrollPeriod $period, float $basicSalary): array
    {
        $otPaymentEnabled = $this->payrollConfigurationService->getValue('ot_payment_enabled', $period->end_date, false, $employee->company_id);

        $totalMinutes = $attendanceStats['overtime_minutes'] ?? 0;

        // Force cast string to boolean if stored as string "true" or "false"
        if ($otPaymentEnabled === 'false' || $otPaymentEnabled === '0' || $otPaymentEnabled === 0) {
            $otPaymentEnabled = false;
        } elseif ($otPaymentEnabled === 'true' || $otPaymentEnabled === '1' || $otPaymentEnabled === 1) {
            $otPaymentEnabled = true;
        }

        if (!$otPaymentEnabled) {
            return [
                'ot_payment_enabled' => false,
                'ot_rule_configured' => false,
                'ot_minutes_calculated' => $totalMinutes,
                'ot_hours_calculated' => round($totalMinutes / 60, 2),
                'ot_formula_version' => null,
                'ot_formula_type' => null,
                'ot_rate_applied' => null,
                'ot_multiplier_applied' => null,
                'ot_amount' => 0.00,
            ];
        }

        // Configuration checks
        $formulaType = $this->payrollConfigurationService->getValue('ot_formula_type', $period->end_date, null, $employee->company_id);
        $multiplier = $this->payrollConfigurationService->getValue('ot_multiplier', $period->end_date, null, $employee->company_id);
        $divisor = $this->payrollConfigurationService->getValue('ot_divisor', $period->end_date, null, $employee->company_id);
        
        $holidayRate = $this->payrollConfigurationService->getValue('ot_holiday_multiplier', $period->end_date, null, $employee->company_id);
        $offDayRate = $this->payrollConfigurationService->getValue('ot_off_day_multiplier', $period->end_date, null, $employee->company_id);
        $weekendRate = $this->payrollConfigurationService->getValue('ot_weekend_multiplier', $period->end_date, null, $employee->company_id);
        
        if (!$formulaType || !$multiplier || !$divisor) {
             return [
                'ot_payment_enabled' => true,
                'ot_rule_configured' => false,
                'ot_minutes_calculated' => $totalMinutes,
                'ot_hours_calculated' => round($totalMinutes / 60, 2),
                'ot_formula_version' => null,
                'ot_formula_type' => null,
                'ot_rate_applied' => null,
                'ot_multiplier_applied' => null,
                'ot_amount' => 0.00,
            ];
        }

        $formulaType = (string)$formulaType;
        $multiplier = (float)$multiplier;
        $divisor = (float)$divisor;

        // Framework for different model calculations
        $hourlyRate = 0.00;
        
        if ($formulaType === 'MODEL_A' || $formulaType === 'MODEL_B') {
            $hourlyRate = $basicSalary / max($divisor, 1);
        } else if ($formulaType === 'MODEL_C') {
            $fixedRate = (float)$this->payrollConfigurationService->getValue('ot_fixed_rate', $period->end_date, 0.00, $employee->company_id);
            $hourlyRate = $fixedRate;
        }

        $totalAmount = 0.00;
        
        $normalHours = ($attendanceStats['normal_overtime_minutes'] ?? 0) / 60;
        $holidayHours = ($attendanceStats['holiday_overtime_minutes'] ?? 0) / 60;
        $offDayHours = ($attendanceStats['off_day_overtime_minutes'] ?? 0) / 60;
        $weekendHours = ($attendanceStats['weekend_overtime_minutes'] ?? 0) / 60;

        $totalAmount += $normalHours * $hourlyRate * $multiplier;
        if ($holidayRate !== null) $totalAmount += $holidayHours * $hourlyRate * (float)$holidayRate;
        if ($offDayRate !== null) $totalAmount += $offDayHours * $hourlyRate * (float)$offDayRate;
        if ($weekendRate !== null) $totalAmount += $weekendHours * $hourlyRate * (float)$weekendRate;
        
        $minimumMinutes = (int)$this->payrollConfigurationService->getValue('ot_minimum_minutes', $period->end_date, 0, $employee->company_id);
        $maximumMinutesVal = $this->payrollConfigurationService->getValue('ot_maximum_minutes', $period->end_date, null, $employee->company_id);
        $maximumMinutes = $maximumMinutesVal !== null ? (int)$maximumMinutesVal : PHP_INT_MAX;

        if ($totalMinutes > 0 && $totalMinutes < $minimumMinutes) {
            $totalAmount = 0.00;
        }
        
        $calculatedMinutes = $totalMinutes;
        
        if ($totalMinutes > $maximumMinutes && $maximumMinutes > 0) {
            $ratio = $maximumMinutes / $totalMinutes;
            $totalAmount *= $ratio;
            $calculatedMinutes = $maximumMinutes;
        }

        return [
            'ot_payment_enabled' => true,
            'ot_rule_configured' => true,
            'ot_minutes_calculated' => $calculatedMinutes,
            'ot_hours_calculated' => round($calculatedMinutes / 60, 2),
            'ot_formula_version' => '1.0',
            'ot_formula_type' => $formulaType,
            'ot_rate_applied' => round($hourlyRate, 2),
            'ot_multiplier_applied' => $multiplier,
            'ot_amount' => round($totalAmount, 2),
        ];
    }
}
