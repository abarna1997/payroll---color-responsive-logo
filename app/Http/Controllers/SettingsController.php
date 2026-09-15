<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * View Payroll Settings page.
     * Only Super Administrator and HR Administrator can access this page.
     */
    public function payroll()
    {
        if (! Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to manage Settings.');
        }

        $readOnly = (Auth::user()->role !== 'Super Administrator');

        $settings = [
            'Currency' => Setting::getVal('Payroll', 'Currency', 'LKR'),
            'DecimalPlaces' => Setting::getVal('Payroll', 'DecimalPlaces', '2'),
            'CurrencyPosition' => Setting::getVal('Payroll', 'CurrencyPosition', 'Before'),
            'SalaryDaysPerMonth' => Setting::getVal('Payroll', 'SalaryDaysPerMonth', '30'),
            'WorkingHoursPerMonth' => Setting::getVal('Payroll', 'WorkingHoursPerMonth', '240'),
            'PayrollCycle' => Setting::getVal('Payroll', 'PayrollCycle', 'Monthly'),

            'EnableEPF' => Setting::getVal('Payroll', 'EnableEPF', 'true'),
            'EnableETF' => Setting::getVal('Payroll', 'EnableETF', 'true'),
            'EPFEmployeeRate' => Setting::getVal('Payroll', 'EPFEmployeeRate', '8.00'),
            'EPFEmployerRate' => Setting::getVal('Payroll', 'EPFEmployerRate', '12.00'),
            'ETFRate' => Setting::getVal('Payroll', 'ETFRate', '3.00'),

            'AttendanceBasedPayroll' => Setting::getVal('Payroll', 'AttendanceBasedPayroll', 'true'),
            'LateGrace' => Setting::getVal('Payroll', 'LateGrace', '15'),
            'EarlyOutGrace' => Setting::getVal('Payroll', 'EarlyOutGrace', '15'),
            'AttendanceBonusEnabled' => Setting::getVal('Payroll', 'AttendanceBonusEnabled', 'false'),

            'OvertimeMultiplier' => Setting::getVal('Payroll', 'OvertimeMultiplier', '1.5'),
            'EnableOvertime' => Setting::getVal('Payroll', 'EnableOvertime', 'true'),
            'WeekendOTMultiplier' => Setting::getVal('Payroll', 'WeekendOTMultiplier', '1.5'),
            'HolidayOTMultiplier' => Setting::getVal('Payroll', 'HolidayOTMultiplier', '2.0'),
            'PoyaOTMultiplier' => Setting::getVal('Payroll', 'PoyaOTMultiplier', '2.0'),

            'NoPayFormula' => Setting::getVal('Payroll', 'NoPayFormula', 'BASIC_DIV_30'),

            'LoanAutoDeduction' => Setting::getVal('Payroll', 'LoanAutoDeduction', 'true'),
            'AdvanceSalaryDeduction' => Setting::getVal('Payroll', 'AdvanceSalaryDeduction', 'true'),

            'PayrollApprovalRequired' => Setting::getVal('Payroll', 'PayrollApprovalRequired', 'true'),
            'PayrollLockAfterApproval' => Setting::getVal('Payroll', 'PayrollLockAfterApproval', 'true'),

            'EnableAPIT' => Setting::getVal('Payroll', 'EnableAPIT', 'false'),
            'GratuityEnabled' => Setting::getVal('Payroll', 'GratuityEnabled', 'false'),
            'LeaveEncashment' => Setting::getVal('Payroll', 'LeaveEncashment', 'false'),
        ];

        return view('settings.payroll', compact('settings', 'readOnly'));
    }

    /**
     * Update Payroll Settings.
     * Only Super Administrator can perform this action.
     */
    public function updatePayroll(Request $request)
    {
        if (! Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to modify Settings.');
        }

        $request->validate([
            'Currency' => 'required|string|size:3',
            'DecimalPlaces' => 'required|integer|between:0,4',
            'SalaryDaysPerMonth' => 'required|integer|between:1,31',
            'WorkingHoursPerMonth' => 'required|integer|between:1,744',
            'EPFEmployeeRate' => 'required|numeric|between:0,100',
            'EPFEmployerRate' => 'required|numeric|between:0,100',
            'ETFRate' => 'required|numeric|between:0,100',
            'OvertimeMultiplier' => 'required|numeric|min:1',
            'WeekendOTMultiplier' => 'required|numeric|min:1',
            'HolidayOTMultiplier' => 'required|numeric|min:1',
            'PoyaOTMultiplier' => 'required|numeric|min:1',
            'LateGrace' => 'required|integer|min:0',
            'EarlyOutGrace' => 'required|integer|min:0',
        ]);

        $keysToSave = [
            'Currency', 'DecimalPlaces', 'CurrencyPosition', 'SalaryDaysPerMonth', 'WorkingHoursPerMonth', 'PayrollCycle',
            'EPFEmployeeRate', 'EPFEmployerRate', 'ETFRate', 'LateGrace', 'EarlyOutGrace',
            'OvertimeMultiplier', 'WeekendOTMultiplier', 'HolidayOTMultiplier', 'PoyaOTMultiplier', 'NoPayFormula',
        ];

        $checkboxKeys = [
            'EnableEPF', 'EnableETF', 'EnableOvertime', 'AttendanceBasedPayroll', 'AttendanceBonusEnabled',
            'LoanAutoDeduction', 'AdvanceSalaryDeduction', 'PayrollApprovalRequired', 'PayrollLockAfterApproval',
            'EnableAPIT', 'GratuityEnabled', 'LeaveEncashment',
        ];

        $changedKeys = [];

        foreach ($keysToSave as $key) {
            $oldVal = Setting::getVal('Payroll', $key);
            $newVal = (string) $request->input($key);
            if ($oldVal !== $newVal) {
                Setting::setVal('Payroll', $key, $newVal);
                Cache::forget("payroll_setting_{$key}");
                $changedKeys[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        foreach ($checkboxKeys as $key) {
            $oldVal = Setting::getVal('Payroll', $key);
            $newVal = $request->has($key) ? 'true' : 'false';
            if ($oldVal !== $newVal) {
                Setting::setVal('Payroll', $key, $newVal);
                Cache::forget("payroll_setting_{$key}");
                $changedKeys[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        if (count($changedKeys) > 0) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Payroll Settings Updated',
                'module' => 'Payroll Settings',
                'record_id' => 0,
                'old_value' => null,
                'new_value' => json_encode($changedKeys),
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->back()->with('success', 'Payroll Settings updated successfully.');
    }
}
