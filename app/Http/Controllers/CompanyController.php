<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\AuditLog;
use App\Services\CompanyService;
use App\Models\Employee;
use App\Jobs\SyncEmployeeToDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(Request $request)
    {
        $query = Company::withCount('branches', 'employees');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('company_code', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->latest()->get();

        $metrics = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', 'Active')->count(),
            'inactive_companies' => Company::where('status', '!=', 'Active')->count(),
            'total_branches' => \App\Models\Branch::count(),
            'total_employees' => \App\Models\Employee::count(),
            'total_devices' => \App\Models\Device::count(),
        ];

        return view('companies.index', compact('companies', 'metrics'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_code' => 'required|string|unique:companies,company_code',
            'company_name' => 'required|string',
            'registration_number' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $company = Company::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_COMPANY',
            'module' => 'Company Management',
            'record_id' => $company->id,
            'new_value' => json_encode($company),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Company created successfully!');
    }

    public function edit($id)
    {
        if (!Auth::user()->hasPermissionTo('manage_companies')) {
            abort(403, 'Unauthorized action. You do not have permission to view Company Settings.');
        }

        $company = Company::findOrFail($id);
        $readOnly = (Auth::user()->role !== 'Super Administrator');

        return view('companies.edit', compact('company', 'readOnly'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('manage_companies')) {
            abort(403, 'Unauthorized action. You do not have permission to modify Company Settings.');
        }

        $company = Company::findOrFail($id);

        $request->validate([
            'company_code' => 'required|string|unique:companies,company_code,' . $id,
            'company_name' => 'required|string',
            'registration_number' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
            'status' => 'required|string|in:Active,Inactive',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'header_banner' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'footer_banner' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'watermark' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'company_seal' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'company_stamp' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'email_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'email_footer_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'hr_signature' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'finance_signature' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'director_signature' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'ceo_signature' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'authorized_signature' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $this->companyService->updateCompany($company, $request);

        return redirect()->route('companies.edit', $company->id)->with('success', 'Company Profile & Branding updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $oldVal = json_encode($company);
        $company->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_COMPANY',
            'module' => 'Company Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Company deleted successfully!');
    }

    public function syncCompany(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'Active')
            ->get();

        $count = 0;
        foreach ($employees as $employee) {
            if ($employee->devices()->count() > 0) {
                SyncEmployeeToDevices::dispatch($employee->id);
                $count++;
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'SYNC_ALL_COMPANY_EMPLOYEES',
            'module' => 'Company Management',
            'record_id' => $company->id,
            'description' => "Dispatched sync for {$count} employees of {$company->company_name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Sync dispatched for {$count} active employees of {$company->company_name}.");
    }
}
