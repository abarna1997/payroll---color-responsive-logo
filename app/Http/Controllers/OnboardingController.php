<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\AgreementTemplate;
use App\Models\EmployeeDocument;
use App\Models\EmployeeAgreement;
use App\Models\EmployeeAsset;
use App\Models\OnboardingStage;
use App\Models\EmployeeChecklist;
use App\Models\OnboardingNotification;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OnboardingController extends Controller
{
    /**
     * Onboarding Status Dashboard
     */
    public function dashboard()
    {
        $onboardingEmployees = Employee::where('employment_status', 'Onboarding')
            ->orderBy('created_at', 'desc')
            ->get();

        $templatesCount = AgreementTemplate::count();
        
        $pendingDocs = EmployeeDocument::where('status', 'Pending')->count();
        $pendingAgreements = EmployeeAgreement::where('status', 'Generated')->count();
        $pendingApprovals = OnboardingStage::where('status', 'Pending')->count();

        // New joiners (in onboarding phase or recently confirmed within 30 days)
        $newJoiners = Employee::where('join_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('join_date', 'desc')
            ->get();

        return view('onboarding.dashboard', compact(
            'onboardingEmployees',
            'templatesCount',
            'pendingDocs',
            'pendingAgreements',
            'pendingApprovals',
            'newJoiners'
        ));
    }

    /**
     * Onboarding Wizard Form
     */
    public function wizard(Request $request)
    {
        $step = (int) $request->input('step', 1);
        $employeeId = $request->input('employee_id');
        $employee = null;

        if ($employeeId) {
            $employee = Employee::with([
                'documents',
                'agreements.template',
                'assets',
                'onboardingStages.approver',
                'checklistItems'
            ])->findOrFail($employeeId);
        }

        $companies = Company::where('status', 'Active')->get();
        $branches = Branch::where('status', 'Active')->get();
        $departments = Department::all();
        $shifts = Shift::all();
        $templates = AgreementTemplate::all();
        $managers = Employee::where('status', 'Active')->get();
        $designations = \App\Models\Designation::where('status', 'Active')->get();

        return view('onboarding.wizard', compact(
            'step',
            'employee',
            'companies',
            'branches',
            'departments',
            'shifts',
            'templates',
            'managers',
            'designations'
        ));
    }

    /**
     * Store Onboarding Wizard Steps
     */
    public function store(Request $request)
    {
        $step = (int) $request->input('step', 1);
        $employeeId = $request->input('employee_id');

        if ($step === 1) {
            // Validate Personal Info
            $request->validate([
                'employee_id_code' => 'required|string|unique:employees,employee_id,' . $employeeId,
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'gender' => 'nullable|string',
                'nic' => 'nullable|string',
                'personal_email' => 'nullable|email',
                'mobile_number' => 'nullable|string',
            ]);

            $data = [
                'employee_id' => $request->input('employee_id_code'),
                'title' => $request->input('title'),
                'first_name' => $request->input('first_name'),
                'middle_name' => $request->input('middle_name'),
                'last_name' => $request->input('last_name'),
                'gender' => $request->input('gender'),
                'nic' => $request->input('nic'),
                'nationality' => $request->input('nationality'),
                'religion' => $request->input('religion'),
                'date_of_birth' => $request->input('date_of_birth'),
                'marital_status' => $request->input('marital_status'),
                'blood_group' => $request->input('blood_group'),
                'personal_email' => $request->input('personal_email'),
                'company_email' => $request->input('company_email'),
                'email' => $request->input('personal_email'), // Fallback email
                'mobile_number' => $request->input('mobile_number'),
                'emergency_contact_name' => $request->input('emergency_contact_name'),
                'emergency_contact_phone' => $request->input('emergency_contact_phone'),
                'emergency_contact_relationship' => $request->input('emergency_contact_relationship'),
                'permanent_address' => $request->input('permanent_address'),
                'current_address' => $request->input('current_address'),
                'employment_status' => 'Onboarding',
                'status' => 'Inactive', // Inactive until onboarding is completed & approved
            ];

            // Handle Profile Photo
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/profiles/'), $filename);
                $data['profile_photo'] = 'uploads/profiles/' . $filename;
            }

            // Handle Drawn Signature Upload
            if ($request->filled('drawn_signature_data')) {
                $signatureData = $request->input('drawn_signature_data');
                $data['signature'] = $signatureData;
            }

            if ($employeeId) {
                $employee = Employee::findOrFail($employeeId);
                $employee->update($data);
            } else {
                // Calculate employee number from trailing digits or auto-increment
                preg_match('/\d+$/', $request->input('employee_id_code'), $matches);
                $data['employee_number'] = isset($matches[0]) ? (int)$matches[0] : 1;
                $data['company_id'] = Company::first()->id ?? 1; // Default placeholders
                $data['department_id'] = Department::first()->id ?? 1;
                
                $employee = Employee::create($data);

                // Initialize Checklist Tasks
                $tasks = [
                    'Documents Uploaded',
                    'Agreement Signed',
                    'Salary Assigned',
                    'Leave Assigned',
                    'Shift Assigned',
                    'Welcome Email Sent'
                ];
                foreach ($tasks as $task) {
                    EmployeeChecklist::create([
                        'employee_id' => $employee->id,
                        'task_name' => $task,
                        'is_completed' => false
                    ]);
                }

                // Initialize Approval Stages
                $stages = ['HR', 'Manager', 'Finance', 'IT', 'Director', 'CEO'];
                foreach ($stages as $stage) {
                    OnboardingStage::create([
                        'employee_id' => $employee->id,
                        'stage_name' => $stage,
                        'status' => 'Pending'
                    ]);
                }

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'INITIATE_ONBOARDING',
                    'module' => 'Onboarding Management',
                    'record_id' => $employee->id,
                    'new_value' => json_encode($employee),
                    'ip_address' => $request->ip()
                ]);
            }

            return redirect()->route('onboarding.wizard', ['step' => 2, 'employee_id' => $employee->id])
                ->with('success', 'Personal info saved successfully.');
        }

        $employee = Employee::findOrFail($employeeId);

        if ($step === 2) {
            // Validate & Store Employment details
            $request->validate([
                'company_id' => 'required|exists:companies,id',
                'department_id' => 'required|exists:departments,id',
            ]);

            $employee->update([
                'company_id' => $request->company_id,
                'branch_id' => $request->branch_id,
                'department_id' => $request->department_id,
                'shift_id' => $request->shift_id,
                'designation' => $request->designation,
                'job_grade' => $request->job_grade,
                'probation_period' => $request->probation_period,
                'confirmation_date' => $request->confirmation_date,
                'work_location' => $request->work_location,
                'work_mode' => $request->work_mode ?? 'NORMAL',
                'allow_remote_punch' => $request->allow_remote_punch ? 1 : 0,
                'home_latitude' => $request->home_latitude,
                'home_longitude' => $request->home_longitude,
                'allowed_radius' => $request->allowed_radius ?? 150,
                'cost_center' => $request->cost_center,
                'payroll_group' => $request->payroll_group,
                'attendance_policy' => $request->attendance_policy,
                'leave_policy' => $request->leave_policy,
                'holiday_calendar' => $request->holiday_calendar,
                'join_date' => $request->join_date,
            ]);

            // Audit Logging for Work Arrangement
            $workModeChanged = $employee->wasChanged(['work_mode', 'allow_remote_punch', 'home_latitude']);
            if ($workModeChanged) {
                \App\Models\AuditLog::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'action' => 'UPDATE_WORK_ARRANGEMENT',
                    'module' => 'Employee Management',
                    'record_id' => $employee->id,
                    'old_value' => 'NORMAL', // Minimal payload for simplicity
                    'new_value' => $employee->work_mode,
                    'ip_address' => request()->ip(),
                    'browser' => request()->header('User-Agent')
                ]);
            }

            // If shift is assigned, tick checklist item
            if ($request->shift_id) {
                EmployeeChecklist::where('employee_id', $employee->id)
                    ->where('task_name', 'Shift Assigned')
                    ->update(['is_completed' => true, 'completed_at' => Carbon::now()]);
            }

            return redirect()->route('onboarding.wizard', ['step' => 3, 'employee_id' => $employee->id])
                ->with('success', 'Employment details saved.');
        }

        if ($step === 3) {
            // Validate & Store Salary Compensation
            $employee->update([
                'bank_name' => $request->input('bank_name'),
                'bank_account' => $request->input('bank_account'),
                'bank_swift' => $request->input('bank_swift'),
                'bank_branch' => $request->input('bank_branch'),
                'currency' => $request->input('currency', 'LKR'),
                'payment_method' => $request->input('payment_method', 'Bank Transfer'),
            ]);

            // Update or Create Salary Profile
            $employee->salaryProfile()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'basic_salary' => $request->input('basic_salary', 0),
                    'incentive'    => $request->input('incentive', 0),
                    'epf_eligible' => true,
                    'etf_eligible' => true,
                ]
            );

            // Tick checklist item
            if ($request->input('basic_salary') > 0) {
                EmployeeChecklist::where('employee_id', $employee->id)
                    ->where('task_name', 'Salary Assigned')
                    ->update(['is_completed' => true, 'completed_at' => Carbon::now()]);
            }

            return redirect()->route('onboarding.wizard', ['step' => 4, 'employee_id' => $employee->id])
                ->with('success', 'Compensation details saved.');
        }

        if ($step === 4) {
            // Documents uploads handled
            $docTypes = [
                'nic_upload' => 'NIC',
                'passport_upload' => 'Passport',
                'birth_upload' => 'Birth Certificate',
                'cv_upload' => 'CV / Resume'
            ];

            $uploadedCount = 0;
            foreach ($docTypes as $inputName => $docName) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $filename = time() . '_' . $employee->id . '_' . $inputName . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/documents/'), $filename);
                    $filePath = 'uploads/documents/' . $filename;

                    // Delete old doc record
                    EmployeeDocument::where('employee_id', $employee->id)
                        ->where('document_name', $docName)
                        ->delete();

                    EmployeeDocument::create([
                        'employee_id' => $employee->id,
                        'document_name' => $docName,
                        'file_path' => $filePath,
                        'expiry_date' => $request->input($inputName . '_expiry'),
                        'status' => 'Uploaded'
                    ]);
                    $uploadedCount++;
                }
            }

            if ($employee->documents()->count() >= 2) {
                EmployeeChecklist::where('employee_id', $employee->id)
                    ->where('task_name', 'Documents Uploaded')
                    ->update(['is_completed' => true, 'completed_at' => Carbon::now()]);
            }

            return redirect()->route('onboarding.wizard', ['step' => 5, 'employee_id' => $employee->id])
                ->with('success', "Uploaded {$uploadedCount} documents.");
        }

        if ($step === 6) {
            // Leave allocation
            // Save allocated days in leaves log if required (or simple checklist tick)
            EmployeeChecklist::where('employee_id', $employee->id)
                ->where('task_name', 'Leave Assigned')
                ->update(['is_completed' => true, 'completed_at' => Carbon::now()]);

            return redirect()->route('onboarding.wizard', ['step' => 7, 'employee_id' => $employee->id])
                ->with('success', 'Leave allocations assigned.');
        }

        if ($step === 8) {
            // Approvals submitted
            return redirect()->route('onboarding.wizard', ['step' => 9, 'employee_id' => $employee->id])
                ->with('success', 'Approval logs generated.');
        }

        if ($step === 9) {
            // Welcome package email dispatch trigger
            EmployeeChecklist::where('employee_id', $employee->id)
                ->where('task_name', 'Welcome Email Sent')
                ->update(['is_completed' => true, 'completed_at' => Carbon::now()]);

            return redirect()->route('onboarding.wizard', ['step' => 10, 'employee_id' => $employee->id])
                ->with('success', 'Welcome checklist items verified.');
        }

        if ($step === 10) {
            // Completed Onboarding submission
            // Mark employee as active if fully approved
            $isFullyApproved = !OnboardingStage::where('employee_id', $employee->id)
                ->where('status', '!=', 'Approved')
                ->exists();

            if ($isFullyApproved) {
                $employee->update([
                    'employment_status' => 'Confirmed',
                    'status' => 'Active'
                ]);
                return redirect()->route('onboarding.dashboard')->with('success', 'Onboarding completed! Employee is now active.');
            }

            return redirect()->route('onboarding.dashboard')->with('success', 'Onboarding complete request submitted. Waiting for pending approvals.');
        }

        return redirect()->route('onboarding.wizard', ['step' => $step + 1, 'employee_id' => $employee->id]);
    }

    /**
     * View Employee Onboarding Profile
     */
    public function profile($id)
    {
        $employee = Employee::with([
            'company', 'branch', 'department', 'shift', 'devices', 'documents',
            'agreements.template', 'assets', 'onboardingStages.approver', 'checklistItems',
            'user.directPermissions', 'user.permissionHistory.editor'
        ])->findOrFail($id);

        $checklistCompleted = $employee->checklistItems->where('is_completed', true)->count();
        $checklistTotal = $employee->checklistItems->count() ?: 1;
        $completionRate = round(($checklistCompleted / $checklistTotal) * 100);

        $matrix = \App\Http\Controllers\PermissionController::getMatrix();

        $roles = \App\Models\Role::orderBy('sort_order', 'asc')->get();
        $accessLevels = \App\Models\AccessLevel::orderBy('level', 'asc')->get();
        $templates = \App\Models\PermissionTemplate::orderBy('name', 'asc')->get();

        return view('onboarding.profile', compact('employee', 'completionRate', 'matrix', 'roles', 'accessLevels', 'templates'));
    }

    /**
     * Approve Stage Workflow
     */
    public function approveStage(Request $request, $id)
    {
        $request->validate([
            'stage_name' => 'required|string',
            'status' => 'required|in:Approved,Rejected',
            'comments' => 'nullable|string'
        ]);

        $employee = Employee::findOrFail($id);

        $stage = OnboardingStage::where('employee_id', $id)
            ->where('stage_name', $request->stage_name)
            ->firstOrFail();

        $stage->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
            'comments' => $request->comments
        ]);

        OnboardingNotification::create([
            'employee_id' => $employee->id,
            'title' => "Stage Approved: {$request->stage_name}",
            'message' => "Onboarding stage {$request->stage_name} was marked as {$request->status} by " . Auth::user()->username
        ]);

        // Auto confirm employee if all stages are approved
        $pending = OnboardingStage::where('employee_id', $employee->id)
            ->where('status', '!=', 'Approved')
            ->exists();

        if (!$pending) {
            $employee->update([
                'employment_status' => 'Confirmed',
                'status' => 'Active'
            ]);
        }

        return back()->with('success', "Stage {$request->stage_name} updated successfully.");
    }

    /**
     * Assign Asset to Onboarding Employee
     */
    public function assignAsset(Request $request, $id)
    {
        $request->validate([
            'asset_name' => 'required|string',
            'serial_number' => 'nullable|string',
            'assigned_date' => 'required|date'
        ]);

        $employee = Employee::findOrFail($id);

        $asset = EmployeeAsset::create([
            'employee_id' => $employee->id,
            'asset_name' => $request->asset_name,
            'serial_number' => $request->serial_number,
            'assigned_date' => $request->assigned_date,
            'status' => 'Assigned'
        ]);

        // Auto generate asset handover form path (PDF placeholder simulation)
        $asset->update([
            'handover_form_path' => 'uploads/handovers/asset_' . $asset->id . '.pdf'
        ]);

        return back()->with('success', "Asset {$request->asset_name} assigned successfully.");
    }

    /**
     * Return Asset
     */
    public function returnAsset(Request $request, $id, $assetId)
    {
        $asset = EmployeeAsset::where('employee_id', $id)->findOrFail($assetId);
        $asset->update([
            'status' => 'Returned',
            'returned_date' => Carbon::now()->toDateString()
        ]);

        return back()->with('success', "Asset returned successfully.");
    }

    /**
     * Generate PDF Agreement from Template
     */
    public function generateAgreement(Request $request, $id)
    {
        $request->validate([
            'template_id' => 'required|exists:agreement_templates,id'
        ]);

        $employee = Employee::with('company', 'branch', 'department')->findOrFail($id);
        $template = AgreementTemplate::findOrFail($request->template_id);

        // Dynamic placeholder variable replacements
        $replacements = [
            '{{company_name}}' => $employee->company->company_name ?? 'AMS Ltd',
            '{{employee_name}}' => $employee->full_name,
            '{{employee_id}}' => $employee->employee_id,
            '{{designation}}' => $employee->designation ?? 'Staff',
            '{{department}}' => $employee->department->department_name ?? 'N/A',
            '{{salary}}' => $employee->currency . ' ' . number_format($employee->basic_salary, 2),
            '{{join_date}}' => $employee->join_date ? $employee->join_date->format('Y-m-d') : Carbon::now()->toDateString(),
            '{{address}}' => $employee->current_address ?? 'N/A',
            '{{nic}}' => $employee->nic ?? 'N/A',
            '{{today}}' => Carbon::now()->toDateString(),
        ];

        $contentHtml = str_replace(array_keys($replacements), array_values($replacements), $template->content_html);

        $agreement = EmployeeAgreement::create([
            'employee_id' => $employee->id,
            'template_id' => $template->id,
            'status' => 'Generated'
        ]);

        // Generate PDF using DOMPDF Facade
        $pdfPath = 'uploads/agreements/agreement_' . $agreement->id . '.pdf';
        
        // Ensure folder exists
        if (!file_exists(public_path('uploads/agreements'))) {
            mkdir(public_path('uploads/agreements'), 0777, true);
        }

        $pdf = Pdf::loadHTML("<div style='font-family: sans-serif; color: #333; line-height: 1.6; padding: 20px;'>{$contentHtml}</div>");
        $pdf->save(public_path($pdfPath));

        $agreement->update([
            'file_path' => $pdfPath
        ]);

        return back()->with('success', "Agreement \"{$template->name}\" compiled and generated.");
    }

    /**
     * Sign Agreement
     */
    public function signAgreement(Request $request, $id, $agreementId)
    {
        $request->validate([
            'signature_type' => 'required|in:drawn,typed',
            'signature_data' => 'required|string'
        ]);

        $agreement = EmployeeAgreement::where('employee_id', $id)->findOrFail($agreementId);

        $agreement->update([
            'status' => 'Signed',
            'signature_type' => $request->signature_type,
            'signature_data' => $request->signature_data,
            'signed_at' => Carbon::now()
        ]);

        // Tick checklist item
        EmployeeChecklist::where('employee_id', $id)
            ->where('task_name', 'Agreement Signed')
            ->update(['is_completed' => true, 'completed_at' => Carbon::now()]);

        return back()->with('success', 'Document signed successfully.');
    }

    /**
     * Download PDF File
     */
    public function downloadAgreement($agreementId)
    {
        $agreement = EmployeeAgreement::findOrFail($agreementId);
        if ($agreement->file_path && file_exists(public_path($agreement->file_path))) {
            return response()->download(public_path($agreement->file_path));
        }
        return back()->with('error', 'Agreement PDF file not found.');
    }

    /**
     * Download Asset Handover Sheet
     */
    public function downloadAssetHandover($assetId)
    {
        $asset = EmployeeAsset::with('employee')->findOrFail($assetId);
        
        $html = "
        <div style='font-family: sans-serif; padding: 40px; border: 1px solid #ddd;'>
            <h2 style='text-align: center; text-transform: uppercase; color: #4F46E5;'>Asset Handover Sheet</h2>
            <hr>
            <p><strong>Employee:</strong> {$asset->employee->full_name} ({$asset->employee->employee_id})</p>
            <p><strong>Asset Name:</strong> {$asset->asset_name}</p>
            <p><strong>Serial Number:</strong> " . ($asset->serial_number ?: 'N/A') . "</p>
            <p><strong>Handover Date:</strong> {$asset->assigned_date}</p>
            <p><strong>Status:</strong> {$asset->status}</p>
            <br><br>
            <table style='width: 100%; margin-top: 50px;'>
                <tr>
                    <td style='width: 50%;'>
                        ____________________________<br>
                        <strong>Receiver Signature</strong>
                    </td>
                    <td style='width: 50%; text-align: right;'>
                        ____________________________<br>
                        <strong>Authorized Issuer Signature</strong>
                    </td>
                </tr>
            </table>
        </div>";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download("asset_handover_{$asset->id}.pdf");
    }

    /**
     * Enterprise Document Management Center
     */
    public function templates(Request $request)
    {
        $query = AgreementTemplate::withCount('agreements');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->latest()->get();

        // Telemetry metrics
        $metrics = [
            'total_templates' => AgreementTemplate::count(),
            'active_templates' => AgreementTemplate::count(), // Default all existing active
            'generated_documents' => EmployeeAgreement::count(),
            'signed_documents' => EmployeeAgreement::where('status', 'Signed')->count(),
            'pending_signatures' => EmployeeAgreement::where('status', 'Generated')->count(),
            'expired_documents' => EmployeeAgreement::where('status', 'Expired')->count(),
        ];

        $companies = Company::where('status', 'Active')->get();
        $departments = Department::all();

        return view('onboarding.templates', compact('templates', 'metrics', 'companies', 'departments'));
    }

    /**
     * Store Agreement Template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'content_html' => 'required|string'
        ]);

        AgreementTemplate::create($request->all());

        return back()->with('success', 'Agreement template created successfully.');
    }

    /**
     * Update Agreement Template
     */
    public function updateTemplate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'content_html' => 'required|string'
        ]);

        $template = AgreementTemplate::findOrFail($id);
        $template->update($request->all());

        return back()->with('success', 'Agreement template updated successfully.');
    }

    /**
     * Delete Template
     */
    public function destroyTemplate($id)
    {
        $template = AgreementTemplate::findOrFail($id);
        $template->delete();

        return back()->with('success', 'Agreement template deleted.');
    }

    /**
     * Update Employee Security Settings and User Link (V3.2)
     */
    public function updateSecurity(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $enableLogin = $request->has('enable_login');

        if ($enableLogin) {
            $user = $employee->user;

            $request->validate([
                'username' => 'required|string|max:255|unique:users,username,' . ($user ? $user->id : 'NULL'),
                'email' => 'required|email|unique:users,email,' . ($user ? $user->id : 'NULL'),
                'password' => $user ? 'nullable|string|min:8' : 'required|string|min:8',
                'role' => 'required|string|exists:roles,name',
                'access_level_id' => 'nullable|exists:access_levels,id',
                'template_id' => 'nullable|exists:permission_templates,id',
            ]);

            if ($user) {
                // Update existing user
                $user->username = $request->username;
                $user->email = $request->email;
                $user->role = $request->role;
                $user->access_level_id = $request->access_level_id;
                $user->template_id = $request->template_id;
                $user->status = $request->input('status', 'Active');
                $user->force_password_change = $request->has('force_password_change');

                if ($request->filled('password')) {
                    $user->password = bcrypt($request->password);
                }

                $user->save();
            } else {
                // Create user
                $user = \App\Models\User::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role' => $request->role,
                    'access_level_id' => $request->access_level_id,
                    'template_id' => $request->template_id,
                    'status' => $request->input('status', 'Active'),
                    'force_password_change' => $request->has('force_password_change'),
                ]);

                // Link to employee
                $employee->user_id = $user->id;
                $employee->save();
            }

            // Handle Direct Permission Overrides
            $inputs = $request->input('permissions', []);
            $reason = $request->input('override_reason', 'Updated from employee security tab');
            
            DB::transaction(function () use ($user, $inputs, $reason) {
                $currentDirect = $user->directPermissions()->get()->pluck('value', 'permission_key')->toArray();
                $matrix = \App\Http\Controllers\PermissionController::getMatrix();

                foreach ($matrix as $group => $items) {
                    foreach ($items as $key => $label) {
                        $newVal = $inputs[$key] ?? 'Inherit';
                        $oldVal = $currentDirect[$key] ?? 'Inherit';

                        if ($newVal !== $oldVal) {
                            // Log history
                            \App\Models\UserPermissionHistory::create([
                                'user_id' => $user->id,
                                'permission_key' => $key,
                                'old_value' => $oldVal,
                                'new_value' => $newVal,
                                'changed_by' => Auth::id(),
                                'ip_address' => request()->ip(),
                                'reason' => $reason,
                            ]);

                            // Save override record
                            if ($newVal === 'Inherit') {
                                $user->directPermissions()->where('permission_key', $key)->delete();
                            } else {
                                $user->directPermissions()->updateOrCreate(
                                    ['permission_key' => $key],
                                    ['value' => $newVal]
                                );
                            }
                        }
                    }
                }
            });

            // Invalidate permission cache
            $user->invalidatePermissionCache();

            // Log Audit Log
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_EMPLOYEE_SECURITY',
                'module' => 'Employee Management',
                'record_id' => $employee->id,
                'new_value' => json_encode(['username' => $user->username, 'role' => $user->role]),
                'ip_address' => $request->ip(),
            ]);

        } else {
            // Disable login
            if ($employee->user) {
                $user = $employee->user;
                $user->status = 'Inactive';
                $user->save();
                $user->invalidatePermissionCache();

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'DISABLE_EMPLOYEE_LOGIN',
                    'module' => 'Employee Management',
                    'record_id' => $employee->id,
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return back()->with('success', 'Employee security & access settings updated successfully.');
    }

    /**
     * Skip Onboarding (Super Admin Only) & Make Employee Active / Available
     */
    public function skipOnboarding($id)
    {
        if (Auth::user()->role !== 'Super Administrator') {
            abort(403, 'Unauthorized action. Only Super Administrators can bypass onboarding.');
        }

        $employee = Employee::findOrFail($id);
        $employee->employment_status = 'Active';
        $employee->status = 'Active';
        $employee->save();

        // Queue ADMS User Creation across assigned devices
        $assignedDeviceIds = \Illuminate\Support\Facades\DB::table('employee_devices')
            ->where('employee_id', $employee->id)
            ->pluck('device_id')
            ->toArray();

        if (empty($assignedDeviceIds) && $employee->company_id) {
            $assignedDeviceIds = \App\Models\Device::where('company_id', $employee->company_id)->pluck('id')->toArray();
        }

        \App\Jobs\SyncEmployeeToDevices::dispatchSync($employee->id, $assignedDeviceIds, 'create');

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'SKIP_ONBOARDING',
            'module' => 'Onboarding Management',
            'record_id' => $employee->id,
            'new_value' => json_encode(['employment_status' => 'Active', 'status' => 'Active']),
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('onboarding.profile', $employee->id)
            ->with('success', "Onboarding bypassed! Employee {$employee->full_name} is now Active and available across the system.");
    }
}
