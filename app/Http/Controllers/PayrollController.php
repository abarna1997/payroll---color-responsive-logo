<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\SalaryProfile;
use App\Services\Payroll\PayrollConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected $workflowService;
    protected $configService;

    public function __construct(\App\Services\Payroll\PayrollWorkflowService $workflowService, PayrollConfigurationService $configService)
    {
        $this->workflowService = $workflowService;
        $this->configService = $configService;
    }

    // Lists all payroll periods
    public function index()
    {
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();

        // Calculate metrics
        $metrics = [
            'period_name' => 'No active period',
            'employees_included' => 0,
            'payroll_cost' => 0,
            'total_epf' => 0,
            'total_etf' => 0,
            'total_ot' => 0,
        ];
        
        $latestPeriod = PayrollPeriod::orderBy('id', 'desc')->first();
        if ($latestPeriod) {
            $metrics['period_name'] = $latestPeriod->period_name;
            $payslips = Payslip::where('payroll_period_id', $latestPeriod->id)->get();
            $metrics['employees_included'] = $payslips->count();
            $metrics['payroll_cost'] = $payslips->sum('net_salary');
            
            $epfEmp = $payslips->sum('epf_employee');
            $epfEmpr = $payslips->sum('epf_employer');
            $metrics['total_epf'] = $epfEmp + $epfEmpr;
            $metrics['total_etf'] = $payslips->sum('etf_employer');
            $metrics['total_ot'] = $payslips->sum('ot_amount');
        }

        // Get currency setting
        $currencySetting = \App\Models\Setting::where('group_name', 'Payroll')
            ->where('setting_key', 'Currency')
            ->first();
        $currency = $currencySetting ? $currencySetting->setting_value : 'LKR';

        // Actual Trend Data
        $trendLabels = [];
        $trendData = [];
        $processedPeriods = $periods->whereIn('status', ['Processed', 'Approved', 'Locked'])->take(6)->reverse();
        foreach ($processedPeriods as $p) {
            $trendLabels[] = $p->period_name;
            $trendData[] = Payslip::where('payroll_period_id', $p->id)->sum('net_salary');
        }

        return view('payroll.index', compact('periods', 'metrics', 'currency', 'trendLabels', 'trendData'));
    }

    // Creates a new payroll period
    public function createPeriod(Request $request)
    {
        $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cycle_type' => 'required|string',
        ]);

        $period = PayrollPeriod::create([
            'period_name' => $request->period_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'cycle_type' => $request->cycle_type,
            'status' => 'Draft',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_PAYROLL_PERIOD',
            'module' => 'Payroll',
            'record_id' => $period->id,
            'new_value' => json_encode($period),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('payroll.index')->with('success', 'Payroll Period created successfully.');
    }

    // Delete a draft payroll period
    public function destroyPeriod($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        
        if ($period->status !== 'Draft') {
            return redirect()->back()->with('error', 'Only Draft payroll periods can be deleted.');
        }

        // Payslips restrict deletion on DB level, so we must manually cascade delete them first
        $period->payslips()->delete();
        
        // Log the deletion before removing the model
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_PAYROLL_PERIOD',
            'module' => 'Payroll',
            'record_id' => $period->id,
            'new_value' => json_encode(['period_name' => $period->period_name]),
            'ip_address' => request()->ip(),
        ]);

        $period->delete();

        return redirect()->route('payroll.index')->with('success', 'Payroll Period deleted successfully.');
    }

    // Update payroll period details
    public function updatePeriod(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        
        if (!in_array($period->status, ['Draft', 'Revision Required'])) {
            return redirect()->back()->with('error', 'Only Draft or Revision Required payroll periods can be edited.');
        }

        $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cycle_type' => 'required|string',
        ]);

        $old = $period->toArray();
        $period->update([
            'period_name' => $request->period_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'cycle_type' => $request->cycle_type,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_PAYROLL_PERIOD',
            'module' => 'Payroll',
            'record_id' => $period->id,
            'old_value' => json_encode($old),
            'new_value' => json_encode($period->toArray()),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Payroll Period updated successfully.');
    }


    // Show details of a payroll run
    public function showPeriod($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $payslips = Payslip::with('employee.department', 'employee.branch')->where('payroll_period_id', $id)->get();

        // List active employees without a payslip in this period
        $processedEmployeeIds = $payslips->pluck('employee_id')->toArray();
        $employeeQuery = Employee::where('status', 'Active')
            ->whereNotIn('id', $processedEmployeeIds);

        if ($period->company_id) {
            $employeeQuery->where('company_id', $period->company_id);
        }

        $unprocessedEmployees = $employeeQuery->get();

        $missingProfiles = collect();
        $profileService = app(\App\Services\Payroll\PayrollSalaryProfileService::class);
        foreach ($unprocessedEmployees as $emp) {
            if (!$profileService->resolveForPeriod($emp, $period)) {
                $missingProfiles->push($emp);
            }
        }

        return view('payroll.period_view', compact('period', 'payslips', 'unprocessedEmployees', 'missingProfiles'));
    }

    // Process payroll in bulk
    public function processPeriod(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        
        Gate::authorize('process', $period);

        if (!in_array($period->status, ['Draft', 'Revision Required'])) {
            return redirect()->back()->with('error', 'Payroll can only be processed when Draft or Revision Required.');
        }

        if ($period->company_id) {
            $employees = Employee::where('status', 'Active')->where('company_id', $period->company_id)->get();
        } else {
            $employees = Employee::where('status', 'Active')->get();
        }
        $processedCount = 0;
        $skipErrors = [];
        
        // Preload all attendance and leave data for the period to avoid N+1 queries
        app(\App\Services\Payroll\PayrollAttendanceService::class)->preloadForPeriod($period);
        
        $calculationService = app(\App\Services\Payroll\PayrollCalculationService::class);

        foreach ($employees as $employee) {
            $errors = $calculationService->calculate($period, $employee);
            if (is_array($errors) && count($errors) > 0) {
                // Collect the first error for this employee to show the user why they were skipped
                $skipErrors[] = "{$employee->full_name}: " . implode(', ', $errors);
            } else {
                $processedCount++;
            }
        }

        $period->update([
            'status' => 'Processed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'PROCESS_PAYROLL_PERIOD_BULK',
            'module' => 'Payroll',
            'record_id' => $period->id,
            'new_value' => json_encode(['processed_employees' => $processedCount]),
            'ip_address' => $request->ip(),
        ]);

        $msg = "Payroll computed successfully for {$processedCount} active employees.";
        if (count($skipErrors) > 0) {
            $msg .= " However, " . count($skipErrors) . " employees were skipped due to missing setup.";
            return redirect()->route('payroll.show', $period->id)
                ->with('success', $msg)
                ->with('error', "Skipped Employees:\n" . implode("\n", $skipErrors));
        }

        return redirect()->route('payroll.show', $period->id)->with('success', $msg);
    }

    // Recalculates individual employee payslip
    public function recalculateEmployee(Request $request, $periodId, $employeeId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        
        Gate::authorize('process', $period);

        if (in_array($period->status, ['Locked', 'Approved', 'HR Review'])) {
            return redirect()->back()->with('error', "Payroll is {$period->status} for this period and cannot be recalculated.");
        }

        $employee = Employee::findOrFail($employeeId);
        app(\App\Services\Payroll\PayrollCalculationService::class)->calculate($period, $employee);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'RECALCULATE_INDIVIDUAL_PAYROLL',
            'module' => 'Payroll',
            'record_id' => $periodId,
            'new_value' => json_encode(['employee_id' => $employeeId]),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Payroll recalculated for {$employee->full_name}.");
    }

    public function submitForReview(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        Gate::authorize('process', $period);

        try {
            $this->workflowService->submitForReview($period);
            return redirect()->back()->with('success', 'Payroll period submitted for HR Review.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function requestRevision(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        Gate::authorize('review', $period);

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $this->workflowService->requestRevision($period, $request->reason);
            return redirect()->back()->with('success', 'Revision requested.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Approve the payroll period
    public function approvePeriod(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        Gate::authorize('approve', $period);

        try {
            $this->workflowService->approve($period, $request->input('comment'));
            return redirect()->back()->with('success', 'Payroll period has been approved.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Lock the payroll period (immutable)
    public function lockPeriod(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        Gate::authorize('lock', $period);

        try {
            $this->workflowService->lock($period, $request->input('reason'));
            return redirect()->back()->with('success', 'Payroll period locked. Records are now immutable.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    // Create Revision for locked period
    public function createRevision(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        Gate::authorize('revise', $period);

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $newPeriod = $this->workflowService->createRevision($period, $request->reason);
            return redirect()->route('payroll.show', $newPeriod->id)->with('success', "Revision {$newPeriod->revision_number} created.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Individual payslip details
    public function payslip($id)
    {
        $payslip = Payslip::with('employee.company', 'employee.branch', 'employee.department', 'period')->findOrFail($id);

        return view('payroll.payslip', compact('payslip'));
    }

    // Download PDF Payslip Export
    public function exportPdf($id)
    {
        $payslip = Payslip::with('employee.company', 'employee.branch', 'employee.department', 'period')->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.payslip_pdf', compact('payslip'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Payslip_' . str_replace(' ', '_', $payslip->employee->company->company_name) . '_' . $payslip->employee->employee_id . '_' . $payslip->period->start_date->format('M_Y') . '.pdf';

        return $pdf->download($filename);
    }

    // Manage employee salary profiles
    public function profiles()
    {
        $employees = Employee::with('salaryProfile', 'salaryProfiles', 'company', 'department', 'branch')
            ->where('status', 'Active')
            ->get();

        $today = now()->toDateString();
        $epfEmpRate = $this->configService->getValue('epf_employee_percentage', $today, 8);
        $epfEmprRate = $this->configService->getValue('epf_employer_percentage', $today, 12);
        $etfRate = $this->configService->getValue('etf_employer_percentage', $today, 3);

        return view('payroll.profiles', compact('employees', 'epfEmpRate', 'epfEmprRate', 'etfRate'));
    }

    // Update employee salary profile
    public function updateProfile(Request $request, $employeeId)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'deductions' => 'nullable|array',
            'effective_from' => 'required|date',
            'tax_employment_type' => 'nullable|string',
            'tax_residency' => 'nullable|string',
            'tax_cumulative_enabled' => 'nullable|boolean',
        ]);

        $employee = Employee::findOrFail($employeeId);
        
        // Update Tax Profile on the Employee model
        if ($request->has('tax_employment_type')) {
            $employee->tax_employment_type = $request->tax_employment_type;
            $employee->tax_residency = $request->tax_residency;
            $employee->tax_cumulative_enabled = $request->has('tax_cumulative_enabled');
            $employee->save();
        }

        $effectiveFrom = $request->effective_from;

        // Process allowances/deductions to map names and amounts
        $allowances = [];
        if ($request->has('allowances')) {
            foreach ($request->allowances as $name => $amount) {
                if ($amount !== null && $amount !== '') {
                    $allowances[] = ['name' => $name, 'amount' => (float) $amount];
                }
            }
        }

        $deductions = [];
        if ($request->has('deductions')) {
            foreach ($request->deductions as $name => $amount) {
                // APIT should be handled by tax profile, remove manual entry
                if ($name === 'APIT') continue;
                
                if ($amount !== null && $amount !== '') {
                    $deductions[] = ['name' => $name, 'amount' => (float) $amount];
                }
            }
        }

        // Close current active profile
        $currentProfile = $employee->salaryProfile;
        $oldValue = null;

        if ($currentProfile) {
            $oldValue = json_encode($currentProfile);
            if ($currentProfile->effective_from < $effectiveFrom) {
                $currentProfile->effective_to = Carbon::parse($effectiveFrom)->subDay()->toDateString();
                $currentProfile->status = 'Historical';
                $currentProfile->save();
            } else {
                // If it's same day or later, just mark historical and insert new one 
                // Or maybe the user is fixing a mistake. Let's just archive the old one.
                $currentProfile->status = 'Historical';
                $currentProfile->save();
            }
        }

        $profile = SalaryProfile::create([
            'employee_id' => $employeeId,
            'company_id' => $employee->company_id,
            'basic_salary' => $request->basic_salary,
            'incentive' => $request->incentive ?? 0,
            'allowances_json' => $allowances,
            'deductions_json' => $deductions,
            'epf_eligible' => $request->has('epf_eligible'),
            'etf_eligible' => $request->has('etf_eligible'),
            'effective_from' => $effectiveFrom,
            'status' => 'Active'
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_SALARY_PROFILE',
            'module' => 'Payroll',
            'record_id' => $profile->id,
            'old_value' => $oldValue,
            'new_value' => json_encode($profile),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('payroll.profiles')->with('success', 'Salary profile updated successfully.');
    }

    // Internal calculation engine
    private function calculatePayslip(PayrollPeriod $period, Employee $employee)
    {
        app(\App\Services\Payroll\PayrollCalculationService::class)->calculate($period, $employee);
    }

    // Redirect to the latest period detail page for processing
    public function processing()
    {
        $latestPeriod = PayrollPeriod::orderBy('id', 'desc')->first();
        if ($latestPeriod) {
            return redirect()->route('payroll.show', $latestPeriod->id);
        }
        return redirect()->route('payroll.index')->with('warning', 'Please initialize a payroll period first.');
    }

    // View all computed payslips in the system
    public function payslips(Request $request)
    {
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();
        
        $query = Payslip::with('employee.department', 'employee.branch', 'period');
        
        if ($request->filled('period_id')) {
            $query->where('payroll_period_id', $request->period_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        $payslips = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get currency setting
        $currencySetting = \App\Models\Setting::where('group_name', 'Payroll')
            ->where('setting_key', 'Currency')
            ->first();
        $currency = $currencySetting ? $currencySetting->setting_value : 'LKR';
        
        return view('payroll.payslips', compact('payslips', 'periods', 'currency'));
    }

    // Bulk update multiple salary profiles
    public function bulkUpdateProfiles(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'effective_from' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|array',
            'deductions' => 'nullable|array',
        ]);

        $employeeIds = $request->employee_ids;
        $effectiveFrom = $request->effective_from;

        if ($request->has('preview')) {
            return $this->previewBulkUpdate($request, $employeeIds);
        }

        DB::beginTransaction();
        try {
            $updatedCount = 0;

            foreach ($employeeIds as $empId) {
                $employee = Employee::find($empId);
                if (!$employee) continue;

                $currentProfile = $employee->salaryProfile;
                $oldValue = null;

                $newProfileData = [
                    'employee_id' => $empId,
                    'company_id' => $employee->company_id,
                    'basic_salary' => 0,
                    'allowances_json' => [],
                    'deductions_json' => [],
                    'epf_eligible' => true,
                    'etf_eligible' => true,
                    'effective_from' => $effectiveFrom,
                    'status' => 'Active'
                ];

                if ($currentProfile) {
                    $oldValue = json_encode($currentProfile);
                    // Carry over old values if not updated
                    $newProfileData['basic_salary'] = $currentProfile->basic_salary;
                    $newProfileData['epf_eligible'] = $currentProfile->epf_eligible;
                    $newProfileData['etf_eligible'] = $currentProfile->etf_eligible;
                    $newProfileData['allowances_json'] = $currentProfile->allowances_json ?? [];
                    $newProfileData['deductions_json'] = $currentProfile->deductions_json ?? [];
                    $newProfileData['incentive'] = $currentProfile->incentive ?? 0;

                    if ($currentProfile->effective_from < $effectiveFrom) {
                        $currentProfile->effective_to = Carbon::parse($effectiveFrom)->subDay()->toDateString();
                        $currentProfile->status = 'Historical';
                        $currentProfile->save();
                    } else {
                        $currentProfile->status = 'Historical';
                        $currentProfile->save();
                    }
                }

                // Update Basic Contractual Salary
                if ($request->has('update_basic_salary')) {
                    $newProfileData['basic_salary'] = $request->basic_salary ?? 0;
                }

                // Update EPF/ETF
                if ($request->has('update_epf_eligible')) {
                    $newProfileData['epf_eligible'] = $request->has('epf_eligible');
                }
                if ($request->has('update_etf_eligible')) {
                    $newProfileData['etf_eligible'] = $request->has('etf_eligible');
                }

                // Update Allowances
                if ($request->has('update_allowances')) {
                    $allowancesMap = collect($newProfileData['allowances_json'])->pluck('amount', 'name')->toArray();
                    if ($request->has('allowances')) {
                        foreach ($request->allowances as $name => $amount) {
                            if ($amount !== null && $amount !== '') {
                                $allowancesMap[$name] = (float) $amount;
                            }
                        }
                    }
                    $allowances = [];
                    foreach ($allowancesMap as $name => $amount) {
                        $allowances[] = ['name' => $name, 'amount' => $amount];
                    }
                    $newProfileData['allowances_json'] = $allowances;
                    if ($request->has('incentive')) {
                        $newProfileData['incentive'] = $request->incentive ?? 0;
                    }
                }

                // Update Deductions
                if ($request->has('update_deductions')) {
                    $deductionsMap = collect($newProfileData['deductions_json'])->pluck('amount', 'name')->toArray();
                    if ($request->has('deductions')) {
                        foreach ($request->deductions as $name => $amount) {
                            if ($name === 'APIT') continue;
                            if ($amount !== null && $amount !== '') {
                                $deductionsMap[$name] = (float) $amount;
                            }
                        }
                    }
                    $deductions = [];
                    foreach ($deductionsMap as $name => $amount) {
                        $deductions[] = ['name' => $name, 'amount' => $amount];
                    }
                    $newProfileData['deductions_json'] = $deductions;
                }

                // Tax Profile Bulk Updates
                if ($request->has('update_tax_profile')) {
                    if ($request->has('tax_employment_type')) {
                        $employee->tax_employment_type = $request->tax_employment_type;
                    }
                    if ($request->has('tax_residency')) {
                        $employee->tax_residency = $request->tax_residency;
                    }
                    if ($request->has('tax_cumulative_enabled_input')) {
                        $employee->tax_cumulative_enabled = $request->has('tax_cumulative_enabled');
                    }
                    $employee->save();
                }

                $profile = SalaryProfile::create($newProfileData);
                $updatedCount++;

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'BULK_UPDATE_SALARY_PROFILE',
                    'module' => 'Payroll',
                    'record_id' => $profile->id,
                    'old_value' => $oldValue,
                    'new_value' => json_encode($profile),
                    'ip_address' => $request->ip(),
                ]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => "Salary profiles updated successfully for {$updatedCount} employees."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }

    private function previewBulkUpdate(Request $request, $employeeIds)
    {
        $previewData = [
            'valid' => 0,
            'warnings' => 0,
            'conflicts' => 0,
            'changes' => []
        ];

        foreach ($employeeIds as $empId) {
            $employee = Employee::find($empId);
            if (!$employee) continue;

            $previewData['valid']++;
            $currentProfile = $employee->salaryProfile;
            
            $empChanges = [
                'name' => $employee->full_name,
                'id' => $employee->employee_id,
                'fields' => []
            ];

            if ($request->has('update_basic_salary')) {
                $oldBasic = $currentProfile ? $currentProfile->basic_salary : 0;
                $newBasic = $request->basic_salary ?? 0;
                if ($oldBasic != $newBasic) {
                    $empChanges['fields'][] = "Basic Salary: {$oldBasic} → {$newBasic}";
                }
            }

            if ($request->has('update_tax_profile')) {
                $empChanges['fields'][] = "Tax Profile Updated";
            }
            
            if ($currentProfile && $currentProfile->effective_from > $request->effective_from) {
                $previewData['warnings']++;
                $empChanges['fields'][] = "Warning: Retroactive change before current version ({$currentProfile->effective_from})";
            }

            if (!empty($empChanges['fields'])) {
                $previewData['changes'][] = $empChanges;
            }
        }

        return response()->json([
            'html' => view('payroll.partials.bulk_preview', compact('previewData', 'request'))->render()
        ]);
    }
}
