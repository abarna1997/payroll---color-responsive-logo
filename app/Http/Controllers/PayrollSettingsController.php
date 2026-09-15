<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Payslip;
use App\Models\PayrollPeriod;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PayrollSettingsController extends Controller
{
    /**
     * Display the Enterprise Payroll Console.
     */
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to access the Payroll Console.');
        }

        $readOnly = (Auth::user()->role !== 'Super Administrator');

        // Load active settings variables
        $settingsKeys = [
            'Currency' => 'LKR',
            'DecimalPlaces' => '2',
            'CurrencyPosition' => 'Before',
            'SalaryDaysPerMonth' => '30',
            'WorkingHoursPerMonth' => '240',
            'PayrollCycle' => 'Monthly',
            'EnableEPF' => 'true',
            'EnableETF' => 'true',
            'EnableAPIT' => 'false',
            'EnableOvertime' => 'true',
            'EPFEmployeeRate' => '8.00',
            'EPFEmployerRate' => '12.00',
            'ETFRate' => '3.00',
            'OvertimeMultiplier' => '1.5',
            'WeekendOTMultiplier' => '1.5',
            'HolidayOTMultiplier' => '2.0',
            'PoyaOTMultiplier' => '2.0',
            'LateGrace' => '15',
            'EarlyOutGrace' => '15',
            'AttendanceBasedPayroll' => 'true',
            'AttendanceBonusEnabled' => 'false',
            'NoPayFormula' => 'BASIC_DIV_30',
            'LoanAutoDeduction' => 'true',
            'AdvanceSalaryDeduction' => 'true',
            'PayrollApprovalRequired' => 'true',
            'PayrollLockAfterApproval' => 'true',
            'DefaultWorkingDays' => 'Monday-Friday',
        ];

        $settings = [];
        foreach ($settingsKeys as $key => $default) {
            $settings[$key] = Setting::getVal('Payroll', $key, $default);
        }

        // Dashboard Metrics
        $totalEmployees = Employee::where('status', 'Active')->count();
        $latestPeriod = PayrollPeriod::orderBy('id', 'desc')->first();
        
        $metrics = [
            'period_name' => $latestPeriod ? $latestPeriod->period_name : 'No active period',
            'employees_included' => $latestPeriod ? Payslip::where('payroll_period_id', $latestPeriod->id)->count() : 0,
            'payroll_cost' => 0.00,
            'total_epf' => 0.00,
            'total_etf' => 0.00,
            'total_ot' => 0.00,
            'total_loans' => 0.00,
            'total_advances' => 0.00,
        ];

        if ($latestPeriod) {
            $slips = Payslip::where('payroll_period_id', $latestPeriod->id)->get();
            foreach ($slips as $slip) {
                $metrics['payroll_cost'] += (float)$slip->net_salary;
                $metrics['total_epf'] += (float)$slip->epf_employee + (float)$slip->epf_employer;
                $metrics['total_etf'] += (float)$slip->etf_employer;
                $metrics['total_ot'] += (float)$slip->ot_payment;
                
                // Decode deductions array
                if (is_array($slip->deductions_json)) {
                    foreach ($slip->deductions_json as $ded) {
                        if ($ded['name'] === 'Loan') {
                            $metrics['total_loans'] += (float)$ded['amount'];
                        }
                        if ($ded['name'] === 'Advance') {
                            $metrics['total_advances'] += (float)$ded['amount'];
                        }
                    }
                }
            }
        }

        // Custom Dynamic Salary Components
        $companies = Company::all();
        $authorizedCompanyIds = [];
        if (Auth::user()->role === 'Super Administrator') {
            $authorizedCompanyIds = $companies->pluck('id')->toArray();
        } else if (Auth::user()->employee) {
            $authorizedCompanyIds = [Auth::user()->employee->company_id];
            $companies = Company::whereIn('id', $authorizedCompanyIds)->get();
        }

        $selectedCompanyId = request('company_id', $authorizedCompanyIds[0] ?? null);
        if ($selectedCompanyId && !in_array($selectedCompanyId, $authorizedCompanyIds)) {
            $selectedCompanyId = $authorizedCompanyIds[0] ?? null;
        }

        $customComponents = [];
        if ($selectedCompanyId) {
            $customComponents = \App\Models\SalaryComponent::where('company_id', $selectedCompanyId)
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'company_id' => $c->company_id,
                        'code' => $c->code,
                        'name' => $c->name,
                        'type' => $c->type,
                        'calc_type' => $c->calc_type,
                        'value' => $c->default_value,
                        'formula' => $c->default_formula,
                        'is_active' => (bool)$c->is_active,
                    ];
                })->toArray();
        }

        // Audit Logs
        $auditLogs = AuditLog::where('module', 'Company Management')
            ->orWhere('action', 'like', '%Payroll%')
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();

        $employees = Employee::where('status', 'Active')
            ->where('company_id', $selectedCompanyId)
            ->get();

        return view('payroll.admin.index', compact(
            'settings', 'readOnly', 'metrics', 'customComponents', 'auditLogs', 'companies', 'employees', 'authorizedCompanyIds', 'selectedCompanyId'
        ));
    }

    /**
     * Save/Update settings group.
     */
    public function updateSection(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to modify settings.');
        }

        $section = $request->input('section');
        $changedKeys = [];

        // Checkbox keys list to automatically handle missing boolean inputs
        $checkboxKeys = [
            'EnableEPF', 'EnableETF', 'EnableAPIT', 'EnableOvertime',
            'AttendanceBasedPayroll', 'AttendanceBonusEnabled', 'LoanAutoDeduction',
            'AdvanceSalaryDeduction', 'PayrollApprovalRequired', 'PayrollLockAfterApproval'
        ];

        $oldSettings = [];
        $inputs = $request->except(['_token', 'section']);

        foreach ($checkboxKeys as $cb) {
            if ($request->has('section_checkboxes') && in_array($cb, $request->input('section_checkboxes', []))) {
                $inputs[$cb] = $request->has($cb) ? 'true' : 'false';
            }
        }

        foreach ($inputs as $key => $val) {
            if ($key === 'section_checkboxes') continue;
            
            $oldVal = Setting::getVal('Payroll', $key);
            if ($oldVal !== $val) {
                Setting::setVal('Payroll', $key, (string)$val);
                Cache::forget("payroll_setting_{$key}");
                $changedKeys[$key] = ['old' => $oldVal, 'new' => $val];
            }
        }

        if (!empty($changedKeys)) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_PAYROLL_CONFIG_' . strtoupper($section),
                'module' => 'Payroll Center',
                'record_id' => 0,
                'old_value' => json_encode($oldSettings),
                'new_value' => json_encode($changedKeys),
                'ip_address' => $request->ip(),
            ]);
        }

        return back()->with('success', 'Payroll Center config updated successfully!');
    }

    /**
     * Real-time AJAX live preview generator.
     */
    public function preview(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $employee = Employee::findOrFail($employeeId);

        // Fetch latest payslip for this employee
        $payslip = Payslip::where('employee_id', $employee->id)->orderBy('id', 'desc')->first();
        if (!$payslip) {
            // Generate a mock payslip for demonstration if no real slip is in DB
            $payslip = new Payslip();
            $payslip->employee_id = $employee->id;
            $payslip->basic_salary = 30000;
            $payslip->gross_salary = 35000;
            $payslip->net_salary = 32600;
            $payslip->epf_employee = 2400;
            $payslip->epf_employer = 3600;
            $payslip->etf_employer = 900;
            $payslip->no_pay_deduction = 0.00;
            $payslip->ot_payment = 0.00;
            $payslip->allowances_json = [['name' => 'Performance', 'amount' => 5000]];
            $payslip->deductions_json = [];
            $payslip->verification_hash = hash('sha256', uniqid());
            
            $payslip->setRelation('employee', $employee);
            
            $latestPeriod = PayrollPeriod::orderBy('id', 'desc')->first();
            if ($latestPeriod) {
                $payslip->payroll_period_id = $latestPeriod->id;
            } else {
                $period = new PayrollPeriod();
                $period->start_date = now()->startOfMonth();
                $period->end_date = now()->endOfMonth();
                $period->period_name = now()->format('F Y');
                $payslip->payroll_period_id = 0;
            }
        }

        // Mock company parameters dynamically for instant rendering feedback
        $company = $payslip->employee->company;
        if (!$company) {
            return response('Employee does not have a company assigned.', 422);
        }

        $company = clone $company; // Clone to prevent modifying global instance

        $company->color_theme = $request->input('color_theme', $company->color_theme);
        $company->secondary_color = $request->input('secondary_color', $company->secondary_color);
        $company->font_family = $request->input('font_family', $company->font_family);
        
        $company->show_header_banner = $request->input('show_header_banner') === '1';
        $company->show_footer_banner = $request->input('show_footer_banner') === '1';
        $company->show_watermark = $request->input('show_watermark') === '1';
        $company->show_company_seal = $request->input('show_company_seal') === '1';
        $company->signature_mode = $request->input('signature_mode', $company->signature_mode);

        $employee->setRelation('company', $company);
        $payslip->setRelation('employee', $employee);

        return view('payroll.payslip', compact('payslip'));
    }

    /**
     * Sync Sri Lankan public holidays from Nager API or fallback list.
     */
    public function syncHolidays(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            return response()->json(['error' => 'Unauthorized action. You do not have permission to sync holidays.'], 403);
        }

        $year = date('Y');
        $holidays = [];

        try {
            $response = Http::timeout(8)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/LK");
            if ($response->successful()) {
                $holidays = $response->json();
            }
        } catch (\Exception $e) {
            // fail-silent and use rich fallback array
        }

        // Complete Sri Lankan Holiday backup dictionary
        if (empty($holidays)) {
            $holidays = [
                ['name' => 'Tamil Thai Pongal Day', 'date' => "{$year}-01-14"],
                ['name' => 'Duruthu Full Moon Poya Day', 'date' => "{$year}-01-23"],
                ['name' => 'National Day', 'date' => "{$year}-02-04"],
                ['name' => 'Navam Full Moon Poya Day', 'date' => "{$year}-02-22"],
                ['name' => 'Good Friday', 'date' => "{$year}-04-03"],
                ['name' => 'Sinhala & Tamil New Year Day', 'date' => "{$year}-04-13"],
                ['name' => 'Bak Full Moon Poya Day', 'date' => "{$year}-04-22"],
                ['name' => 'May Day', 'date' => "{$year}-05-01"],
                ['name' => 'Vesak Full Moon Poya Day', 'date' => "{$year}-05-24"],
                ['name' => 'Poson Full Moon Poya Day', 'date' => "{$year}-06-21"],
                ['name' => 'Id-Ul-Alha (Hadji Festival Day)', 'date' => "{$year}-07-10"],
                ['name' => 'Nikini Full Moon Poya Day', 'date' => "{$year}-08-19"],
                ['name' => 'Binara Full Moon Poya Day', 'date' => "{$year}-09-17"],
                ['name' => 'Vap Full Moon Poya Day', 'date' => "{$year}-10-16"],
                ['name' => 'Deepavali Festival Day', 'date' => "{$year}-11-08"],
                ['name' => 'Milad-Un-Nabi (Holy Prophet Birthday)', 'date' => "{$year}-12-12"],
                ['name' => 'Christmas Day', 'date' => "{$year}-12-25"],
            ];
        }

        $companies = Company::pluck('id');
        $addedCount = 0;

        foreach ($holidays as $h) {
            $name = $h['localName'] ?? $h['name'];
            $date = $h['date'];

            foreach ($companies as $companyId) {
                $exists = Holiday::where('company_id', $companyId)
                    ->where('holiday_date', $date)
                    ->exists();

                if (!$exists) {
                    Holiday::create([
                        'company_id' => $companyId,
                        'holiday_name' => $name,
                        'holiday_date' => $date,
                        'description' => 'Synchronized Sri Lankan public holiday.',
                    ]);
                    $addedCount++;
                }
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'SYNC_SRILANKAN_HOLIDAYS',
            'module' => 'Payroll Center',
            'record_id' => 0,
            'new_value' => "Synced {$addedCount} holidays.",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Holiday Sync Complete! Added {$addedCount} new holiday dates successfully."
        ]);
    }

    /**
     * Save dynamic customized components settings.
     */
    public function saveComponents(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized action. You do not have permission to modify settings.');
        }

        $components = $request->input('components', []);
        
        $companyId = $request->input('company_id');
        if (!$companyId && Auth::user()->employee) {
            $companyId = Auth::user()->employee->company_id;
        }

        if (!$companyId) {
            return back()->with('error', 'Company scope is required to save salary components.');
        }

        // Only allow saving for authorized companies
        $companies = Company::all();
        $authorizedCompanyIds = [];
        if (Auth::user()->role === 'Super Administrator') {
            $authorizedCompanyIds = $companies->pluck('id')->toArray();
        } else if (Auth::user()->employee) {
            $authorizedCompanyIds = [Auth::user()->employee->company_id];
        }

        if (!in_array($companyId, $authorizedCompanyIds)) {
            abort(403, 'Unauthorized action for this company.');
        }

        $activeCodes = [];
        foreach ($components as $comp) {
            if (empty($comp['code']) || empty($comp['name'])) continue;
            
            $code = strtoupper($comp['code']);
            $activeCodes[] = $code;
            
            \App\Models\SalaryComponent::updateOrCreate(
                ['company_id' => $companyId, 'code' => $code],
                [
                    'name' => $comp['name'],
                    'type' => $comp['type'] ?? 'Allowance',
                    'calc_type' => $comp['calc_type'] ?? 'Fixed',
                    'default_value' => ($comp['calc_type'] ?? 'Fixed') === 'Fixed' ? (float)($comp['formula'] ?? 0) : null,
                    'default_formula' => ($comp['calc_type'] ?? 'Fixed') === 'Formula' ? ($comp['formula'] ?? '') : null,
                    'is_active' => ($comp['is_active'] ?? '1') === '1',
                ]
            );
        }

        // Deactivate components that are no longer in the payload
        \App\Models\SalaryComponent::where('company_id', $companyId)
            ->whereNotIn('code', $activeCodes)
            ->update(['is_active' => false]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_SALARY_COMPONENTS',
            'module' => 'Payroll Center',
            'record_id' => $companyId,
            'new_value' => json_encode($activeCodes),
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Dynamic Salary Components saved successfully for the company.');
    }

}
