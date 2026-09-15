<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceEventLog;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\ManualLog;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserPermissionHistory;
use App\Jobs\SyncEmployeeToDevices;
use App\Services\EmployeeService;
use App\Services\NetworkScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }
    /**
     * Dashboard View (with dynamic stats, health panel, and graphs)
     */

    /**
     * Companies CRUD
     */





    /**
     * Branches CRUD
     */




    /**
     * Departments CRUD
     */








    /**
     * Shifts CRUD
     */




    /**
     * Employees CRUD
     */
    public function employees(Request $request)
    {
        $query = Employee::with('company', 'branch', 'department', 'shift', 'secondaryShift', 'devices');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('device_user_id', 'like', "%{$search}%")
                    ->orWhere('sync_pin', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($dq) use ($search) {
                        $dq->where('department_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company', function ($cq) use ($search) {
                        $cq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('biometric_status')) {
            $query->where('biometric_status', $request->input('biometric_status'));
        }

        $employees = $query->orderBy('id', 'desc')->get();

        // Metrics for summary cards
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'Active')->count(),
            'inactive' => Employee::where('status', '!=', 'Active')->count(),
            'synced' => Employee::where('biometric_status', 'User Synchronized')->count(),
            'pending_sync' => Employee::where('biometric_status', 'Pending Sync')->count(),
            'online_devices' => Device::where('last_seen', '>=', now()->subMinutes(15))->count(),
            'offline_devices' => Device::where('last_seen', '<', now()->subMinutes(15))->orWhereNull('last_seen')->count(),
        ];

        $companies = Company::where('status', 'Active')->get();
        $branches = Branch::where('status', 'Active')->get();
        $departments = Department::all();
        $shifts = Shift::all();
        $devices = Device::all();
        $roles = Role::where('status', 'Active')->get();
        $designations = \App\Models\Designation::where('status', 'Active')->get();

        return view('employees.index', compact('employees', 'stats', 'companies', 'branches', 'departments', 'shifts', 'devices', 'roles', 'designations'));
    }

    public function createEmployee()
    {
        $companies = Company::where('status', 'Active')->get();
        $branches = Branch::where('status', 'Active')->get();
        $departments = Department::all();
        $shifts = Shift::all();
        $devices = Device::all();
        $roles = Role::where('status', 'Active')->get();
        $designations = \App\Models\Designation::where('status', 'Active')->get();

        return view('employees.create', compact('companies', 'branches', 'departments', 'shifts', 'devices', 'roles', 'designations'));
    }

    public function showEmployee($id)
    {
        $employee = Employee::with('company', 'branch', 'department', 'shift', 'secondaryShift', 'devices')->findOrFail($id);
        $companies = Company::where('status', 'Active')->get();
        $branches = Branch::where('status', 'Active')->get();
        $departments = Department::all();
        $shifts = Shift::all();
        $devices = Device::all();
        
        return view('employees.show', compact('employee', 'companies', 'branches', 'departments', 'shifts', 'devices'));
    }

    public function getNextEmployeeId($companyId)
    {
        $company = Company::find($companyId);
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        $maxNum = Employee::where('company_id', $companyId)->max('employee_number');
        $nextNumber = $maxNum ? $maxNum + 1 : 1;
        $prefix = $company->company_code ?? 'EMP';
        
        $nextId = sprintf("%s-%03d", $prefix, $nextNumber);
        
        return response()->json([
            'company_code' => $company->company_code,
            'next_number' => $nextNumber,
            'next_id' => $nextId
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'secondary_shift_id' => 'nullable|exists:shifts,id',
            'employee_id' => 'required|string|unique:employees,employee_id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nic' => 'nullable|string',
            'email' => 'nullable|email',
            'mobile_number' => 'nullable|string',
            'designation' => 'nullable|string',
            'card_number' => 'nullable|string',
            'privilege' => 'nullable|integer',
            'join_date' => 'nullable|date',
            'profile_photo' => [
                'nullable',
                'bail',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $fail('The profile photo must be a JPG or PNG image.');
                        return;
                    }
                    if (function_exists('getimagesize')) {
                        $size = @getimagesize($value->getRealPath());
                        if ($size === false) {
                            $fail('The uploaded file is not a valid image.');
                        }
                    }
                }
            ],
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:devices,id',
            'create_account' => 'nullable',
            'role' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        $emp = $this->employeeService->storeEmployee($request);

        return back()->with('success', "Employee created successfully with ID: {$emp->employee_id}!");
    }

    public function destroyEmployee(Request $request, $id)
    {
        $emp = Employee::findOrFail($id);
        $this->employeeService->destroyEmployee($emp, $request);

        return back()->with('success', 'Employee profile deleted and removal queued across assigned devices!');
    }

    public function updateEmployee(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info("EMPLOYEE_UPDATE_START: ID $id");
        try {
            $emp = Employee::findOrFail($id);
            \Illuminate\Support\Facades\Log::info("EMPLOYEE_FOUND: ID $id");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("EMPLOYEE_NOT_FOUND: ID $id. " . $e->getMessage());
            throw $e;
        }

        try {
            \Illuminate\Support\Facades\Log::info("VALIDATION_START: ID $id");
            $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'secondary_shift_id' => 'nullable|exists:shifts,id',
            'employee_id' => 'required|string|unique:employees,employee_id,' . $id,
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nic' => 'nullable|string',
            'email' => 'nullable|email',
            'mobile_number' => 'nullable|string',
            'designation' => 'nullable|string',
            'card_number' => 'nullable|string',
            'privilege' => 'nullable|integer',
            'join_date' => 'nullable|date',
            'profile_photo' => [
                'nullable',
                'bail',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $fail('The profile photo must be a JPG or PNG image.');
                        return;
                    }
                    if (function_exists('getimagesize')) {
                        $size = @getimagesize($value->getRealPath());
                        if ($size === false) {
                            $fail('The uploaded file is not a valid image.');
                        }
                    }
                }
            ],
            'status' => 'required|string|in:Active,Inactive,Resigned,Terminated',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:devices,id',
            'create_account' => 'nullable',
            'role' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);
        \Illuminate\Support\Facades\Log::info("VALIDATION_COMPLETE: ID $id");
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error("VALIDATION_FAILED: ID $id. " . json_encode($e->errors()));
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("VALIDATION_FATAL_ERROR: ID $id. " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return back()->with('error', 'Update Failed during validation: ' . $e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Log::info("EMPLOYEE_DATABASE_UPDATE_START: ID $id");
            if ($request->hasFile('profile_photo')) {
                \Illuminate\Support\Facades\Log::info("PHOTO_UPLOAD_DETECTED: ID $id");
            }
            $this->employeeService->updateEmployee($emp, $request);
            \Illuminate\Support\Facades\Log::info("EMPLOYEE_UPDATE_FINISHED: ID $id");
            return back()->with('success', 'Employee updated & device synchronization dispatched!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("EMPLOYEE_UPDATE_FAILED: ID $id. Exception: " . get_class($e) . " Message: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Update Failed: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
    }

    public function bulkImportEmployees(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        if (count($data) < 2) {
            return back()->with('error', 'CSV file is empty or missing data rows.');
        }
        
        // Remove header row
        $headers = array_shift($data);

        $successCount = 0;
        $errorCount = 0;

        foreach ($data as $index => $row) {
            // Pad array to avoid undefined offset
            $row = array_pad($row, 18, null);

            $empNo = trim($row[0] ?? '');
            $epfNo = trim($row[1] ?? '');
            
            // Auto use EPF for Employee Number if missing
            if (empty($empNo) && !empty($epfNo)) {
                $empNo = $epfNo;
            }
            
            if (empty($empNo)) {
                $errorCount++;
                continue; // Cannot proceed without an identifier
            }

            $firstName = trim($row[2] ?? '');
            $lastName = trim($row[3] ?? '');
            $gender = trim($row[4] ?? '');
            $dob = trim($row[5] ?? '');
            $nic = trim($row[6] ?? '');
            $email = trim($row[7] ?? '');
            $mobile = trim($row[8] ?? '');
            $designation = trim($row[9] ?? '');
            $joinDate = trim($row[10] ?? '');
            $departmentStr = trim($row[11] ?? '');
            $branchStr = trim($row[12] ?? '');
            $shiftStr = trim($row[13] ?? '');
            $admsUserId = trim($row[14] ?? '');
            $admsPin = trim($row[15] ?? '');

            // Auto generate ADMS details based on EPF
            if (empty($admsUserId) && !empty($epfNo)) {
                $admsUserId = $epfNo;
            }
            if (empty($admsPin) && !empty($epfNo)) {
                $admsPin = $epfNo;
            }

            // Map Date Formats
            $parsedDob = null;
            if (!empty($dob)) {
                try {
                    $parsedDob = Carbon::parse(str_replace(['.', '/'], '-', $dob))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignore invalid
                }
            }

            $parsedJoinDate = null;
            if (!empty($joinDate)) {
                try {
                    $parsedJoinDate = Carbon::parse(str_replace(['.', '/'], '-', $joinDate))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            // Map Branch to company_id
            $companyId = null;
            $branchId = null;
            $departmentId = null;

            if ($request->filled('company_id')) {
                // User explicitly selected a target company in the modal
                $companyId = $request->input('company_id');
                // Use first branch and department of this company
                $branch = Branch::where('company_id', $companyId)->first();
                if ($branch) $branchId = $branch->id;
                
                $dept = Department::where('company_id', $companyId)->first();
                if ($dept) $departmentId = $dept->id;
            } else if (!empty($branchStr)) {
                // Rely on CSV string mapping
                $branch = Branch::where('branch_name', 'like', "%{$branchStr}%")->first();
                if ($branch) {
                    $branchId = $branch->id;
                    $companyId = $branch->company_id;
                    
                    // Fallback department
                    $dept = Department::where('company_id', $companyId)->first();
                    if ($dept) {
                        $departmentId = $dept->id;
                    }
                }
            }

            try {
                Employee::updateOrCreate(
                    ['employee_number' => $empNo], // Match by employee_number
                    [
                        'employee_id' => $epfNo,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'gender' => $gender,
                        'date_of_birth' => $parsedDob,
                        'nic' => $nic,
                        'email' => $email,
                        'mobile_number' => $mobile,
                        'designation' => $designation,
                        'join_date' => $parsedJoinDate,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'department_id' => $departmentId,
                        'device_user_id' => $admsUserId,
                        'sync_pin' => $admsPin,
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Failed to import employee row $index: " . $e->getMessage());
                $errorCount++;
            }
        }

        return back()->with('success', "CSV Import Complete: $successCount employees processed, $errorCount failed.");
    }

    public function bulkDeleteEmployees(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);
        $ids = $request->input('employee_ids');
        $count = count($ids);
        Employee::whereIn('id', $ids)->delete();
        Log::info("Bulk deleted $count employees");
        return back()->with('success', "Successfully deleted $count employees!");
    }

    public function exportEmployees(Request $request)
    {
        $query = Employee::with(['company', 'branch', 'department', 'shift', 'secondaryShift']);

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('device_user_id', 'like', "%{$search}%")
                  ->orWhere('nic_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }
        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($biometricStatus = $request->input('biometric_status')) {
            $query->where('biometric_status', $biometricStatus);
        }

        $employees = $query->orderBy('first_name')->get();

        $filename = 'employees_export_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Employee ID', 'Device ID', 'Sync PIN', 'First Name', 'Last Name', 'Email', 
            'Company', 'Branch', 'Department', 'Designation', 'Status', 'Biometric Status', 'Work Mode'
        ];

        $callback = function() use($employees, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->employee_id,
                    $emp->device_user_id,
                    $emp->sync_pin,
                    $emp->first_name,
                    $emp->last_name,
                    $emp->email,
                    $emp->company->company_name ?? '',
                    $emp->branch->branch_name ?? 'Default',
                    $emp->department->department_name ?? '',
                    $emp->designation ?? '',
                    $emp->status,
                    $emp->biometric_status,
                    $emp->work_mode
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkSyncEmployees(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'force' => 'nullable|boolean',
        ]);
        $ids = $request->input('employee_ids');
        $force = $request->input('force', false);
        $count = count($ids);
        $queued = 0;
        
        $commandService = app(\App\Services\Adms\AdmsCommandService::class);

        foreach ($ids as $id) {
            $emp = Employee::find($id);
            if ($emp) {
                if ($force) {
                    $assignedDeviceIds = \Illuminate\Support\Facades\DB::table('employee_devices')
                        ->where('employee_id', $emp->id)
                        ->pluck('device_id')
                        ->toArray();

                    if (empty($assignedDeviceIds)) {
                        $assignedDeviceIds = \App\Models\Device::where('company_id', $emp->company_id)->pluck('id')->toArray();
                        if (empty($assignedDeviceIds)) {
                            $assignedDeviceIds = \App\Models\Device::pluck('id')->toArray();
                        }
                        if (!empty($assignedDeviceIds)) {
                            $emp->devices()->sync($assignedDeviceIds);
                        }
                    }

                    if (!empty($assignedDeviceIds)) {
                        foreach ($assignedDeviceIds as $devId) {
                            $deviceCommand = \App\Models\DeviceCommand::create([
                                'device_id' => $devId,
                                'employee_id' => $emp->id,
                                'command_type' => 'CREATE_USER',
                                'command' => '',
                                'status' => 'pending',
                                'retry_count' => 0,
                            ]);

                            $commandString = $commandService->generateUserUpdateCommand($emp, $deviceCommand->id);
                            $deviceCommand->update(['command' => $commandString]);
                            $queued++;
                        }
                    }
                } else {
                    $emp->save(); // Triggers EmployeeObserver to dispatch sync
                }
            }
        }
        
        if ($force) {
            return back()->with('success', "Force Sync Triggered: Successfully dispatched $queued CREATE_USER commands for $count employees!");
        }
        return back()->with('success', "Successfully dispatched sync commands for $count employees!");
    }

    public function bulkSkipOnboarding(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);
        $ids = $request->input('employee_ids');
        $count = count($ids);
        Employee::whereIn('id', $ids)->update(['employment_status' => 'Active']);
        Log::info("Bulk skipped onboarding for $count employees");
        return back()->with('success', "Successfully skipped onboarding for $count employees!");
    }

    public function bulkUpdateEmployees(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'update_company' => 'nullable|boolean',
            'company_id' => 'required_if:update_company,1|nullable|exists:companies,id',
            'update_branch' => 'nullable|boolean',
            'branch_id' => 'nullable|exists:branches,id',
            'update_department' => 'nullable|boolean',
            'department_id' => 'required_if:update_department,1|nullable|exists:departments,id',
            'update_designation' => 'nullable|boolean',
            'designation' => 'nullable|string',
            'update_shift' => 'nullable|boolean',
            'shift_id' => 'nullable|exists:shifts,id',
            'update_secondary_shift' => 'nullable|boolean',
            'secondary_shift_id' => 'nullable|exists:shifts,id',
            'force_sync' => 'nullable|boolean',
        ]);

        $updateData = [];

        if ($request->has('update_company')) {
            $updateData['company_id'] = $request->input('company_id');
        }
        if ($request->has('update_branch')) {
            $updateData['branch_id'] = $request->input('branch_id');
        }
        if ($request->has('update_department')) {
            $updateData['department_id'] = $request->input('department_id');
        }
        if ($request->has('update_designation')) {
            $updateData['designation'] = $request->input('designation');
        }
        if ($request->has('update_shift')) {
            $updateData['shift_id'] = $request->input('shift_id');
        }
        if ($request->has('update_secondary_shift')) {
            $updateData['secondary_shift_id'] = $request->input('secondary_shift_id');
        }

        $forceSync = $request->input('force_sync') == 1;

        if (empty($updateData) && !$forceSync) {
            return back()->with('error', 'No fields were selected for bulk update.');
        }

        $employeeIds = $request->input('employee_ids');
        $updatedCount = 0;
        $queued = 0;
        $commandService = app(\App\Services\Adms\AdmsCommandService::class);

        foreach ($employeeIds as $id) {
            $emp = Employee::find($id);
            if ($emp) {
                if (!empty($updateData)) {
                    $oldVal = json_encode($emp);
                    $emp->update($updateData);

                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'BULK_UPDATE_EMPLOYEE',
                        'module' => 'Employee Management',
                        'record_id' => $emp->id,
                        'old_value' => $oldVal,
                        'new_value' => json_encode($emp),
                        'ip_address' => $request->ip(),
                    ]);
                    $updatedCount++;
                }

                if ($forceSync) {
                    $assignedDeviceIds = \Illuminate\Support\Facades\DB::table('employee_devices')
                        ->where('employee_id', $emp->id)
                        ->pluck('device_id')
                        ->toArray();

                    if (empty($assignedDeviceIds)) {
                        $assignedDeviceIds = \App\Models\Device::where('company_id', $emp->company_id)->pluck('id')->toArray();
                        if (empty($assignedDeviceIds)) {
                            $assignedDeviceIds = \App\Models\Device::pluck('id')->toArray();
                        }
                        if (!empty($assignedDeviceIds)) {
                            $emp->devices()->sync($assignedDeviceIds);
                        }
                    }

                    if (!empty($assignedDeviceIds)) {
                        foreach ($assignedDeviceIds as $devId) {
                            $deviceCommand = \App\Models\DeviceCommand::create([
                                'device_id' => $devId,
                                'employee_id' => $emp->id,
                                'command_type' => 'CREATE_USER',
                                'command' => '',
                                'status' => 'pending',
                                'retry_count' => 0,
                            ]);

                            $commandString = $commandService->generateUserUpdateCommand($emp, $deviceCommand->id);
                            $deviceCommand->update(['command' => $commandString]);
                            $queued++;
                        }
                    }
                }
            }
        }

        $msg = "Successfully bulk updated {$updatedCount} employees!";
        if ($forceSync) {
            $msg .= " Additionally, dispatched {$queued} CREATE_USER commands for sync.";
        }
        return back()->with('success', $msg);
    }
    public function syncEmployee($id)
    {
        $emp = Employee::findOrFail($id);
        $assignedDeviceIds = DB::table('employee_devices')
            ->where('employee_id', $emp->id)
            ->pluck('device_id')
            ->toArray();

        // If no explicit devices assigned, fallback to device assigned to employee company or all devices
        if (empty($assignedDeviceIds)) {
            $assignedDeviceIds = Device::where('company_id', $emp->company_id)->pluck('id')->toArray();
            if (empty($assignedDeviceIds)) {
                $assignedDeviceIds = Device::pluck('id')->toArray();
            }
            if (!empty($assignedDeviceIds)) {
                $emp->devices()->sync($assignedDeviceIds);
            }
        }

        if (empty($assignedDeviceIds)) {
            return back()->with('error', "No biometric terminals found in the system to sync {$emp->full_name}.");
        }

        // Force queue commands directly
        $commandService = app(\App\Services\Adms\AdmsCommandService::class);
        $queued = 0;

        foreach ($assignedDeviceIds as $devId) {
            $deviceCommand = \App\Models\DeviceCommand::create([
                'device_id' => $devId,
                'employee_id' => $emp->id,
                'command_type' => 'CREATE_USER',
                'command' => '',
                'status' => 'pending',
                'retry_count' => 0,
            ]);

            $commandString = $commandService->generateUserUpdateCommand($emp, $deviceCommand->id);
            $deviceCommand->update(['command' => $commandString]);
            $queued++;
        }

        return back()->with('success', "Force Sync Triggered: Immediately queued CREATE_USER command for {$emp->full_name} across {$queued} terminal(s)!");
    }

    public function triggerRemoteEnrollment(Request $request, $id)
    {
        $emp = Employee::findOrFail($id);
        $type = $request->input('enroll_type', 'fingerprint'); // 'fingerprint', 'face', 'query'
        $fingerId = (int) $request->input('finger_id', 0);
        $deviceId = $request->input('device_id');

        $targetDevices = $deviceId ? [$deviceId] : $emp->devices->pluck('id')->toArray();
        if (empty($targetDevices)) {
            $targetDevices = Device::pluck('id')->toArray();
        }

        if (empty($targetDevices)) {
            return back()->with('error', 'No active biometrics terminal found for remote enrollment command.');
        }

        $commandService = app(\App\Services\Adms\AdmsCommandService::class);
        $pin = $emp->sync_pin ?? preg_replace('/[^0-9]/', '', $emp->device_user_id ?? $emp->employee_id);
        $count = 0;

        foreach ($targetDevices as $devId) {
            $cmdStr = '';
            $cmdType = 'ENROLL_FP';

            if ($type === 'face') {
                $cmdStr = $commandService->generateEnrollFaceCommand((string) $pin);
                $cmdType = 'ENROLL_FACE';
            } elseif ($type === 'query') {
                $cmdStr = $commandService->generateQueryBioDataCommand((string) $pin);
                $cmdType = 'QUERY_BIODATA';
            } else {
                $cmdStr = $commandService->generateEnrollFingerprintCommand((string) $pin, $fingerId);
                $cmdType = 'ENROLL_FP';
            }

            \App\Models\DeviceCommand::create([
                'device_id' => $devId,
                'employee_id' => $emp->id,
                'command_type' => $cmdType,
                'command' => $cmdStr,
                'status' => 'pending',
                'retry_count' => 0,
            ]);
            $count++;
        }

        return back()->with('success', "Remote enrollment command ({$cmdType}) queued for {$emp->full_name} across {$count} terminal(s)!");
    }



    public function getEmployeeSyncStatus($id)
    {
        $emp = Employee::with('devices')->findOrFail($id);
        $assignedDevices = $emp->devices;

        $telemetry = [];
        foreach ($assignedDevices as $dev) {
            $latestCmd = DeviceCommand::where('device_id', $dev->id)
                ->where('employee_id', $emp->id)
                ->orderBy('id', 'desc')
                ->first();

            $telemetry[] = [
                'device_id' => $dev->id,
                'device_name' => $dev->device_name,
                'serial_number' => $dev->device_serial_number,
                'command_type' => $latestCmd ? $latestCmd->command_type : 'NONE',
                'status' => $latestCmd ? ucfirst($latestCmd->status) : 'Not Queued',
                'retry_count' => $latestCmd ? $latestCmd->retry_count : 0,
                'last_sync' => $latestCmd ? ($latestCmd->completed_at ? $latestCmd->completed_at->format('d M Y H:i') : ($latestCmd->sent_at ? 'Sent ' . $latestCmd->sent_at->diffForHumans() : 'Pending')) : 'N/A',
            ];
        }

        return response()->json([
            'employee_name' => $emp->full_name,
            'device_user_id' => $emp->device_user_id ?? $emp->employee_id,
            'devices' => $telemetry
        ]);
    }

    /**
     * Devices CRUD & Actions
     */









    /**
     * Enterprise Attendance Operations Center
     */



    /**
     * Manual Logs (Correction requests)
     */




    /**
     * Settings Panel
     */


    /**
     * Audit Logs List
     */

    /**
     * Backups Panel (using mysqldump)
     */



    /**
     * Dynamic Reports Generator (12 report types)
     */
    /**
     * Compute paired daily attendance record from raw logs according to policy thresholds
     */

    /**
     * Dynamic Reports Generator (12 report types)
     */

    /**
     * CSV Export handler
     */

    /**
     * Holidays Management CRUD
     */




    /**
     * Users & RBAC Management CRUD
     */




    /**
     * Leaves & Leave Types Management CRUD
     */














}
