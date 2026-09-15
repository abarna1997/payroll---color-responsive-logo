<?php

use App\Http\Controllers\Api\AdmsController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\SystemController;

use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PayrollSettingsController;
use App\Http\Controllers\QueueManagerController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AccessLevelController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ApprovalWorkflowController;
use App\Http\Controllers\Wso2Controller;
use Illuminate\Support\Facades\Route;

// 1. ZKTeco ADMS direct fallback endpoints (outside auth)
Route::middleware('throttle:adms')->group(function () {
    Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);
    Route::post('/iclock/cdata', [AdmsController::class, 'receiveData']);
    Route::post('/iclock/querydata', [AdmsController::class, 'receiveQueryData']);
    Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);
    Route::post('/iclock/devicecmd', [AdmsController::class, 'deviceCommandCallback']);
});

// 2. Guest Routes (Authentication)
Route::middleware(['guest', 'throttle:login'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/auth/wso2', [Wso2Controller::class, 'redirectToWso2'])->name('auth.wso2');
    Route::get('/auth/wso2/callback', [Wso2Controller::class, 'handleWso2Callback'])->name('auth.wso2.callback');
});

// 3. Authenticated Routes (Logout & Forced Password Reset)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/password/change', [AuthController::class, 'showPasswordChange'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'passwordChange'])->name('password.change.post');
});

// 4. Authenticated & Password Policy Validated routes (Management Console)
Route::middleware(['auth', 'password_changed'])->group(function () {

    // Dashboard & Custom Enterprise App Launcher
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard')->middleware('permission:dashboard.view');
    Route::get('/apps', function () {
        $user = auth()->user();
        $employee = $user ? $user->employee : null;
        $userRole = $user ? $user->role : 'Employee';

        $apps = \App\Models\AppRegistry::forUserRole($userRole)->get();

        // Extract distinct categories for filter tabs
        $categories = $apps->pluck('category_label', 'category')->unique();

        $eligibility = null;
        if ($employee) {
            $eligibilityService = app(\App\Services\Attendance\WebPunchEligibilityService::class);
            $eligibility = $eligibilityService->canWebPunch($employee);
        }

        return view('launcher.index', compact('user', 'employee', 'apps', 'categories', 'eligibility'));
    })->name('apps.index');

    // Load Enterprise Module Routes
    require __DIR__ . '/organization.php';
    require __DIR__ . '/workforce.php';
    require __DIR__ . '/attendance.php';
    require __DIR__ . '/shifts.php';
    require __DIR__ . '/devices.php';
    require __DIR__ . '/administration.php';

    // ─── Legacy Route Name Aliases (100% Backward Compatibility) ──────────────
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies')->middleware('permission:company.view');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store')->middleware('permission:company.create');
    Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit')->middleware('permission:company.edit');
    Route::post('/companies/{id}/update', [CompanyController::class, 'update'])->name('companies.update')->middleware('permission:company.edit');
    Route::post('/companies/{id}/delete', [CompanyController::class, 'destroy'])->name('companies.delete')->middleware('permission:company.delete');


    Route::get('/branches', [\App\Http\Controllers\BranchController::class, 'index'])->name('branches')->middleware('permission:branch.view');
    Route::post('/branches', [\App\Http\Controllers\BranchController::class, 'store'])->name('branches.store')->middleware('permission:branch.manage');
    Route::post('/branches/{id}/update', [\App\Http\Controllers\BranchController::class, 'update'])->name('branches.update')->middleware('permission:branch.manage');
    Route::post('/branches/{id}/delete', [\App\Http\Controllers\BranchController::class, 'destroy'])->name('branches.delete')->middleware('permission:branch.manage');

    Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments')->middleware('permission:employee.view');
    Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:employee.edit');
    Route::post('/departments/{id}/update', [\App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:employee.edit');
    Route::post('/departments/{id}/delete', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.delete')->middleware('permission:employee.edit');

    Route::get('/designations', [\App\Http\Controllers\DesignationController::class, 'index'])->name('designations')->middleware('permission:employee.view');
    Route::post('/designations', [\App\Http\Controllers\DesignationController::class, 'store'])->name('designations.store')->middleware('permission:employee.edit');
    Route::post('/designations/{id}/update', [\App\Http\Controllers\DesignationController::class, 'update'])->name('designations.update')->middleware('permission:employee.edit');
    Route::post('/designations/{id}/delete', [\App\Http\Controllers\DesignationController::class, 'destroy'])->name('designations.delete')->middleware('permission:employee.edit');

    Route::get('/locations', [\App\Http\Controllers\LocationController::class, 'index'])->name('locations')->middleware('permission:company.view');
    Route::post('/locations', [\App\Http\Controllers\LocationController::class, 'store'])->name('locations.store')->middleware('permission:company.manage');
    Route::post('/locations/{id}/update', [\App\Http\Controllers\LocationController::class, 'update'])->name('locations.update')->middleware('permission:company.manage');
    Route::post('/locations/{id}/delete', [\App\Http\Controllers\LocationController::class, 'destroy'])->name('locations.delete')->middleware('permission:company.manage');

    Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'employees'])->name('employees')->middleware('permission:employee.view');
    Route::get('/employees/create', [\App\Http\Controllers\EmployeeController::class, 'createEmployee'])->name('employees.create')->middleware('permission:employee.create');
    Route::post('/employees', [\App\Http\Controllers\EmployeeController::class, 'storeEmployee'])->name('employees.store')->middleware('permission:employee.create');
    Route::get('/employees/download-template', function () {
        $fileName = 'AMS_Employee_Import_Template.csv';
        $columns = ['Employee_ID', 'First_Name', 'Last_Name', 'Email', 'NIC_Number', 'Mobile', 'Gender', 'DOB', 'Company_Code', 'Branch_Code', 'Department_Name', 'Designation'];
        
        return response()->streamDownload(function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['EMP-001', 'John', 'Doe', 'john@example.com', '901234567V', '+94771234567', 'Male', '1990-01-01', 'CMP1', 'BR1', 'IT', 'Software Engineer']);
            fclose($file);
        }, $fileName, ['Content-Type' => 'text/csv']);
    })->name('employees.downloadTemplate')->middleware('permission:employee.view');
    
    Route::get('/employees/{id}', [\App\Http\Controllers\EmployeeController::class, 'showEmployee'])->name('employees.show')->middleware('permission:employee.view');
    Route::post('/employees/{id}/update', [\App\Http\Controllers\EmployeeController::class, 'updateEmployee'])->name('employees.update')->middleware('permission:employee.edit');
    Route::post('/employees/{id}/delete', [\App\Http\Controllers\EmployeeController::class, 'destroyEmployee'])->name('employees.delete')->middleware('permission:employee.delete');

    Route::get('/transfers', [\App\Http\Controllers\EmployeeTransferController::class, 'index'])->name('transfers')->middleware('permission:employee.view');
    Route::post('/transfers', [\App\Http\Controllers\EmployeeTransferController::class, 'store'])->name('transfers.store')->middleware('permission:employee.edit');
    Route::post('/transfers/{id}/delete', [\App\Http\Controllers\EmployeeTransferController::class, 'destroy'])->name('transfers.delete')->middleware('permission:employee.edit');

    Route::get('/resignations', [\App\Http\Controllers\EmployeeResignationController::class, 'index'])->name('resignations')->middleware('permission:employee.view');
    Route::post('/resignations', [\App\Http\Controllers\EmployeeResignationController::class, 'store'])->name('resignations.store')->middleware('permission:employee.edit');
    Route::post('/resignations/{id}/update-status', [\App\Http\Controllers\EmployeeResignationController::class, 'updateStatus'])->name('resignations.update-status')->middleware('permission:employee.edit');
    Route::post('/resignations/{id}/delete', [\App\Http\Controllers\EmployeeResignationController::class, 'destroy'])->name('resignations.delete')->middleware('permission:employee.edit');

    Route::get('/terminations', [\App\Http\Controllers\EmployeeTerminationController::class, 'index'])->name('terminations')->middleware('permission:employee.view');
    Route::post('/terminations', [\App\Http\Controllers\EmployeeTerminationController::class, 'store'])->name('terminations.store')->middleware('permission:employee.delete');
    Route::post('/terminations/{id}/delete', [\App\Http\Controllers\EmployeeTerminationController::class, 'destroy'])->name('terminations.delete')->middleware('permission:employee.delete');

    // Attendance Variations (Phase 2)
    Route::get('/overtime', [\App\Http\Controllers\OvertimeController::class, 'index'])->name('overtime')->middleware('permission:attendance.view');
    Route::post('/overtime', [\App\Http\Controllers\OvertimeController::class, 'store'])->name('overtime.store')->middleware('permission:attendance.edit');
    Route::post('/overtime/{id}/update-status', [\App\Http\Controllers\OvertimeController::class, 'updateStatus'])->name('overtime.update-status')->middleware('permission:attendance.edit');
    Route::post('/overtime/{id}/delete', [\App\Http\Controllers\OvertimeController::class, 'destroy'])->name('overtime.delete')->middleware('permission:attendance.edit');

    Route::get('/late-arrivals', [\App\Http\Controllers\LateArrivalController::class, 'index'])->name('late-arrivals')->middleware('permission:attendance.view');
    Route::post('/late-arrivals', [\App\Http\Controllers\LateArrivalController::class, 'store'])->name('late-arrivals.store')->middleware('permission:attendance.edit');
    Route::post('/late-arrivals/{id}/update-status', [\App\Http\Controllers\LateArrivalController::class, 'updateStatus'])->name('late-arrivals.update-status')->middleware('permission:attendance.edit');
    Route::post('/late-arrivals/{id}/delete', [\App\Http\Controllers\LateArrivalController::class, 'destroy'])->name('late-arrivals.delete')->middleware('permission:attendance.edit');

    Route::get('/early-departures', [\App\Http\Controllers\EarlyDepartureController::class, 'index'])->name('early-departures')->middleware('permission:attendance.view');
    Route::post('/early-departures', [\App\Http\Controllers\EarlyDepartureController::class, 'store'])->name('early-departures.store')->middleware('permission:attendance.edit');
    Route::post('/early-departures/{id}/update-status', [\App\Http\Controllers\EarlyDepartureController::class, 'updateStatus'])->name('early-departures.update-status')->middleware('permission:attendance.edit');
    Route::post('/early-departures/{id}/delete', [\App\Http\Controllers\EarlyDepartureController::class, 'destroy'])->name('early-departures.delete')->middleware('permission:attendance.edit');

    // Phase 3: Shift Extensions
    Route::get('/shifts/rotations', [\App\Http\Controllers\ShiftRotationController::class, 'index'])->name('shifts.rotations')->middleware('permission:attendance.view');
    Route::post('/shifts/rotations', [\App\Http\Controllers\ShiftRotationController::class, 'store'])->name('shifts.rotations.store')->middleware('permission:attendance.view');
    Route::post('/shifts/rotations/{id}/update-status', [\App\Http\Controllers\ShiftRotationController::class, 'updateStatus'])->name('shifts.rotations.update-status')->middleware('permission:attendance.view');
    Route::post('/shifts/rotations/{id}/delete', [\App\Http\Controllers\ShiftRotationController::class, 'destroy'])->name('shifts.rotations.delete')->middleware('permission:attendance.view');

    Route::get('/shifts/weekly', [\App\Http\Controllers\WeeklyScheduleController::class, 'index'])->name('shifts.weekly')->middleware('permission:attendance.view');
    Route::post('/shifts/weekly', [\App\Http\Controllers\WeeklyScheduleController::class, 'store'])->name('shifts.weekly.store')->middleware('permission:attendance.view');
    Route::post('/shifts/weekly/{id}/delete', [\App\Http\Controllers\WeeklyScheduleController::class, 'destroy'])->name('shifts.weekly.delete')->middleware('permission:attendance.view');

    // Phase 3: Device Extensions
    Route::get('/devices/groups', [\App\Http\Controllers\DeviceGroupController::class, 'index'])->name('devices.groups')->middleware('permission:device.view');
    Route::post('/devices/groups', [\App\Http\Controllers\DeviceGroupController::class, 'store'])->name('devices.groups.store')->middleware('permission:device.view');
    Route::post('/devices/groups/{id}/update', [\App\Http\Controllers\DeviceGroupController::class, 'update'])->name('devices.groups.update')->middleware('permission:device.view');
    Route::post('/devices/groups/{id}/delete', [\App\Http\Controllers\DeviceGroupController::class, 'destroy'])->name('devices.groups.delete')->middleware('permission:device.view');

    Route::get('/devices/settings', [\App\Http\Controllers\DeviceSettingController::class, 'index'])->name('devices.settings')->middleware('permission:admin.settings');
    Route::post('/devices/settings', [\App\Http\Controllers\DeviceSettingController::class, 'store'])->name('devices.settings.store')->middleware('permission:admin.settings');
    Route::post('/devices/settings/{id}/update', [\App\Http\Controllers\DeviceSettingController::class, 'update'])->name('devices.settings.update')->middleware('permission:admin.settings');
    Route::post('/devices/settings/{id}/delete', [\App\Http\Controllers\DeviceSettingController::class, 'destroy'])->name('devices.settings.delete')->middleware('permission:admin.settings');

    Route::post('/employees/bulk-update', [\App\Http\Controllers\EmployeeController::class, 'bulkUpdateEmployees'])->name('employees.bulk-update')->middleware('permission:employee.edit');
    Route::get('/employees/export', [\App\Http\Controllers\EmployeeController::class, 'exportEmployees'])->name('employees.export')->middleware('permission:employee.view');
    Route::post('/employees/bulk-delete', [\App\Http\Controllers\EmployeeController::class, 'bulkDeleteEmployees'])->name('employees.bulk-delete')->middleware('permission:employee.delete');
    Route::post('/employees/bulk-sync', [\App\Http\Controllers\EmployeeController::class, 'bulkSyncEmployees'])->name('employees.bulk-sync')->middleware('permission:employee.edit');
    Route::post('/employees/bulk-skip-onboarding', [\App\Http\Controllers\EmployeeController::class, 'bulkSkipOnboarding'])->name('employees.bulk-skip-onboarding')->middleware('permission:employee.edit');
    Route::post('/employees/bulk-import', [\App\Http\Controllers\EmployeeController::class, 'bulkImportEmployees'])->name('employees.bulk-import')->middleware('permission:employee.create');
    Route::post('/employees/{id}/sync', [\App\Http\Controllers\EmployeeController::class, 'syncEmployee'])->name('employees.sync')->middleware('permission:employee.edit');
    Route::get('/api/employees/next-id', [\App\Http\Controllers\EmployeeController::class, 'getNextEmployeeId']);
    Route::get('/employees/{id}/sync-status', [\App\Http\Controllers\EmployeeController::class, 'getEmployeeSyncStatus'])->name('employees.sync-status')->middleware('permission:employee.view');
    Route::post('/employees/{id}/trigger-enrollment', [\App\Http\Controllers\EmployeeController::class, 'triggerRemoteEnrollment'])->name('employees.enroll')->middleware('permission:employee.edit');
    Route::post('/devices/{id}/sync-employees', [\App\Http\Controllers\DeviceController::class, 'syncDevice'])->name('devices.sync-employees')->middleware('permission:device.edit');
 
    Route::get('/shifts', [\App\Http\Controllers\ShiftController::class, 'index'])->name('shifts')->middleware('permission:attendance.view');
    Route::post('/shifts', [\App\Http\Controllers\ShiftController::class, 'store'])->name('shifts.store')->middleware('permission:attendance.edit');
    Route::post('/shifts/{id}/update', [\App\Http\Controllers\ShiftController::class, 'update'])->name('shifts.update')->middleware('permission:attendance.edit');
    Route::post('/shifts/{id}/delete', [\App\Http\Controllers\ShiftController::class, 'destroy'])->name('shifts.delete')->middleware('permission:attendance.delete');

    Route::get('/devices', [\App\Http\Controllers\DeviceController::class, 'devices'])->name('devices')->middleware('permission:device.view');
    Route::post('/devices/discover', [\App\Http\Controllers\DeviceController::class, 'discoverDevice'])->name('devices.discover')->middleware('permission:device.register');
    Route::post('/devices/{id}/update', [\App\Http\Controllers\DeviceController::class, 'updateDevice'])->name('devices.update')->middleware('permission:device.register');
    Route::post('/devices/{id}/delete', [\App\Http\Controllers\DeviceController::class, 'destroyDevice'])->name('devices.delete')->middleware('permission:device.register');
    Route::post('/devices/{id}/approve', [\App\Http\Controllers\DeviceController::class, 'approveDevice'])->name('devices.approve')->middleware('permission:device.approve');
    Route::post('/devices/{id}/disable', [\App\Http\Controllers\DeviceController::class, 'disableDevice'])->name('devices.disable')->middleware('permission:device.disable');
    Route::post('/devices/{id}/enable', [\App\Http\Controllers\DeviceController::class, 'enableDevice'])->name('devices.enable')->middleware('permission:device.disable');
    Route::post('/devices/{id}/command', [\App\Http\Controllers\DeviceController::class, 'triggerCommand'])->name('devices.command')->middleware('permission:device.commands');

    // WFH Routes
    Route::get('/wfh', [\App\Http\Controllers\WfhManagementController::class, 'index'])->name('wfh.index');
    Route::post('/wfh', [\App\Http\Controllers\WfhManagementController::class, 'store'])->name('wfh.store');
    Route::post('/wfh/{id}/approve', [\App\Http\Controllers\WfhManagementController::class, 'approve'])->name('wfh.approve');
    Route::post('/wfh/{id}/reject', [\App\Http\Controllers\WfhManagementController::class, 'reject'])->name('wfh.reject');
    Route::post('/wfh/{id}/cancel', [\App\Http\Controllers\WfhManagementController::class, 'cancel'])->name('wfh.cancel');

    // Web Punch Route
    Route::post('/attendance/web-punch', [\App\Http\Controllers\Api\WebPunchController::class, 'store'])->name('web-punch.store');

    Route::get('/settings', [SystemController::class, 'settings'])->name('settings');
    Route::post('/settings', [SystemController::class, 'storeSettings'])->name('settings.store');

    Route::get('/audit-logs', [SystemController::class, 'auditLogs'])->name('audit-logs');
});

// ==============================================================================
// EMPLOYEE PORTAL (Phase 2 - Self-Service Workspace)
// ==============================================================================
Route::middleware(['auth', 'password_changed', 'is_employee'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\EmployeePortalController::class, 'dashboard'])->name('dashboard');
        
        // WFH Routes (Migrated from admin workspace)
        Route::get('/wfh', [\App\Http\Controllers\EmployeePortalController::class, 'wfhIndex'])->name('wfh.index');
        Route::post('/wfh', [\App\Http\Controllers\EmployeePortalController::class, 'wfhStore'])->name('wfh.store');
        Route::post('/wfh/{id}/cancel', [\App\Http\Controllers\EmployeePortalController::class, 'wfhCancel'])->name('wfh.cancel');
        
        // Attendance Routes
        Route::get('/attendance', [\App\Http\Controllers\EmployeePortalController::class, 'attendance'])->name('attendance.index');
        
        // Leave Routes
        Route::get('/leave', [\App\Http\Controllers\EmployeePortalController::class, 'leaveIndex'])->name('leave.index');
        Route::post('/leave', [\App\Http\Controllers\EmployeePortalController::class, 'leaveStore'])->name('leave.store');
        
        // Profile Routes
        Route::get('/profile', [\App\Http\Controllers\EmployeePortalController::class, 'profile'])->name('profile.index');
        
        // Remote Web Punch (Migrated from Api/WebPunchController or custom implementation)
        Route::get('/punch', [\App\Http\Controllers\EmployeePortalController::class, 'punchIndex'])->name('punch.index');
        Route::post('/punch', [\App\Http\Controllers\EmployeePortalController::class, 'punchStore'])->name('punch.store');
        
        // Payslips (Migrated from PayrollController)
        Route::get('/payslips', [\App\Http\Controllers\EmployeePortalController::class, 'payslips'])->name('payslips.index');
        Route::get('/payslips/{uuid}', [\App\Http\Controllers\EmployeePortalController::class, 'showPayslip'])->name('payslips.show');
        Route::get('/payslips/{uuid}/pdf', [\App\Http\Controllers\EmployeePortalController::class, 'downloadPayslipPdf'])->name('payslips.pdf');
    });

    Route::prefix('daily-attendance')->name('daily-attendance.')->group(function () {
        Route::get('/', [DailyAttendanceController::class, 'index'])->name('index');
        Route::get('/{id}/details', [DailyAttendanceController::class, 'details'])->name('details');
        Route::get('/export', [DailyAttendanceController::class, 'export'])->name('export');
    });

    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('index');
        Route::get('/fetch', [\App\Http\Controllers\ScheduleController::class, 'fetchSchedules'])->name('fetch');
        Route::post('/override', [\App\Http\Controllers\ScheduleController::class, 'storeOverride'])->name('override.store');
        Route::post('/assignment', [\App\Http\Controllers\ScheduleController::class, 'storeAssignment'])->name('assignment.store');
        Route::post('/weekly', [\App\Http\Controllers\ScheduleController::class, 'storeWeeklySchedule'])->name('weekly.store');
        Route::get('/bulk-assignment/employees', [\App\Http\Controllers\ScheduleController::class, 'bulkAssignmentEmployees'])->name('bulk.employees');
        Route::post('/bulk-assignment/preview', [\App\Http\Controllers\ScheduleController::class, 'bulkAssignmentPreview'])->name('bulk.preview');
        Route::post('/bulk-assignment/execute', [\App\Http\Controllers\ScheduleController::class, 'bulkAssignmentExecute'])->name('bulk.execute');
        // Keep old bulk endpoint for legacy compatibility if needed
        Route::post('/bulk', [\App\Http\Controllers\ScheduleController::class, 'bulkAssign'])->name('bulk');
    });

Route::middleware(['auth', 'password_changed'])->group(function () {
    Route::get('/backups', [SystemController::class, 'backups'])->name('backups');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export');

    Route::get('/holidays', [SystemController::class, 'holidays'])->name('holidays');
    Route::post('/holidays', [SystemController::class, 'storeHoliday'])->name('holidays.store');
    Route::post('/holidays/{id}/update', [SystemController::class, 'updateHoliday'])->name('holidays.update');
    Route::post('/holidays/{id}/delete', [SystemController::class, 'destroyHoliday'])->name('holidays.delete');

    Route::get('/users', [SystemController::class, 'users'])->name('users');
    Route::post('/users', [SystemController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/update', [SystemController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/delete', [SystemController::class, 'destroyUser'])->name('users.delete');

    Route::get('/roles', [SystemController::class, 'roles'])->name('roles');
    Route::post('/roles', [SystemController::class, 'storeRole'])->name('roles.store');
    Route::post('/roles/{id}/update', [SystemController::class, 'updateRole'])->name('roles.update');
    Route::post('/roles/{id}/delete', [SystemController::class, 'destroyRole'])->name('roles.delete');
    Route::post('/roles/{id}/move-up', [SystemController::class, 'moveRoleUp'])->name('roles.move-up');
    Route::post('/roles/{id}/move-down', [SystemController::class, 'moveRoleDown'])->name('roles.move-down');

    // Helper Endpoint for Auto Generation
    Route::get('/api/companies/{id}/next-employee-id', [EmployeeController::class, 'getNextEmployeeId'])->name('api.companies.next-employee-id');
    Route::post('/companies/{id}/sync-all-employees', [CompanyController::class, 'syncCompany'])->name('companies.sync-all-employees')->middleware('permission:manage_companies');

    // System Database Backups Actions
    Route::post('/backups/trigger', [SystemController::class, 'triggerBackup'])->name('backups.trigger');
    Route::get('/backups/{id}/download', [SystemController::class, 'downloadBackup'])->name('backups.download');
    Route::post('/backups/{id}/restore', [SystemController::class, 'restoreBackup'])->name('backups.restore');

    // Phase 6: Advanced Daily Attendance UI (Redirected to Report Center)
    Route::get('/daily-attendance', [\App\Http\Controllers\DailyAttendanceController::class, 'index'])->name('daily-attendance.index');
    Route::get('/daily-attendance/export', [\App\Http\Controllers\DailyAttendanceController::class, 'export'])->name('daily-attendance.export');
    Route::get('/daily-attendance/{id}/details', [\App\Http\Controllers\DailyAttendanceController::class, 'show'])->name('daily-attendance.show');

    // Legacy Attendance
    Route::get('/attendance', [AttendanceController::class, 'attendanceLogs'])->name('attendance');
    Route::get('/attendance/recalculate', [AttendanceController::class, 'recalculateAttendance'])->name('attendance.recalculate');
    Route::post('/attendance/clear-logs', [AttendanceController::class, 'clearAttendanceLogs'])->name('attendance.clear-logs')->middleware('permission:attendance.delete');

    Route::get('/manual-logs', [AttendanceController::class, 'manualLogs'])->name('manual-logs');
    Route::post('/manual-logs', [AttendanceController::class, 'storeManualLog'])->name('manual-logs.store');
    Route::post('/manual-logs/{id}/approve', [AttendanceController::class, 'approveManualLog'])->name('manual-logs.approve');
    Route::post('/manual-logs/{id}/reject', [AttendanceController::class, 'rejectManualLog'])->name('manual-logs.reject');

    // Leaves CRUD
    Route::get('/leaves', [LeaveController::class, 'leaves'])->name('leaves');
    Route::get('/leaves/types', [LeaveController::class, 'leaveTypesPage'])->name('leaves.types');
    Route::get('/leaves/balances', [LeaveController::class, 'leaveBalancesPage'])->name('leaves.balances');
    Route::post('/leaves/balances/bulk-assign', [LeaveController::class, 'bulkAssignLeaves'])->name('leaves.balances.bulk-assign');
    Route::post('/leaves/types', [LeaveController::class, 'storeLeaveType'])->name('leaves.types.store');
    Route::post('/leaves/types/{id}/update', [LeaveController::class, 'updateLeaveType'])->name('leaves.types.update');
    Route::post('/leaves/request', [LeaveController::class, 'storeLeaveRequest'])->name('leaves.request.store');
    Route::post('/leaves/request/{id}/approve', [LeaveController::class, 'approveLeaveRequest'])->name('leaves.request.approve');
    Route::post('/leaves/request/{id}/reject', [LeaveController::class, 'rejectLeaveRequest'])->name('leaves.request.reject');

    // Payroll Management
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/processing', [PayrollController::class, 'processing'])->name('payroll.processing');
    Route::get('/payroll/payslips', [PayrollController::class, 'payslips'])->name('payroll.payslips');
    Route::post('/payroll/periods', [PayrollController::class, 'createPeriod'])->name('payroll.periods.store');
    Route::post('/payroll/periods/{id}/update', [PayrollController::class, 'updatePeriod'])->name('payroll.periods.update');
    Route::delete('/payroll/periods/{id}', [PayrollController::class, 'destroyPeriod'])->name('payroll.periods.destroy');
    Route::get('/payroll/periods/{id}', [PayrollController::class, 'showPeriod'])->name('payroll.show');
    Route::post('/payroll/periods/{id}/process', [PayrollController::class, 'processPeriod'])->name('payroll.periods.process');
    Route::post('/payroll/periods/{id}/recalculate/{employee_id}', [PayrollController::class, 'recalculateEmployee'])->name('payroll.periods.recalculate');
    
    // Workflow Endpoints
    Route::post('/payroll/periods/{id}/submit', [PayrollController::class, 'submitForReview'])->name('payroll.periods.submit');
    Route::post('/payroll/periods/{id}/revision', [PayrollController::class, 'requestRevision'])->name('payroll.periods.revision');
    Route::post('/payroll/periods/{id}/approve', [PayrollController::class, 'approvePeriod'])->name('payroll.periods.approve');
    Route::post('/payroll/periods/{id}/lock', [PayrollController::class, 'lockPeriod'])->name('payroll.periods.lock');
    Route::post('/payroll/periods/{id}/create-revision', [PayrollController::class, 'createRevision'])->name('payroll.periods.create_revision');
    Route::get('/payroll/payslip/{uuid}', [PayrollController::class, 'payslip'])->name('payroll.payslip');
    Route::get('/payroll/profiles', [PayrollController::class, 'profiles'])->name('payroll.profiles');
    Route::post('/payroll/profiles/bulk-update', [PayrollController::class, 'bulkUpdateProfiles'])->name('payroll.profiles.bulk-update');
    Route::post('/payroll/profiles/{id}', [PayrollController::class, 'updateProfile'])->name('payroll.profiles.update');
    Route::get('/settings/payroll', [SettingsController::class, 'payroll'])->name('settings.payroll');
    Route::post('/settings/payroll', [SettingsController::class, 'updatePayroll'])->name('settings.payroll.update');

    // Enterprise Payroll Console (V2.3)
    Route::get('/admin/payroll', [PayrollSettingsController::class, 'index'])->name('admin.payroll.index');
    Route::get('/admin/payroll/business-rules', [PayrollSettingsController::class, 'businessRules'])->name('admin.payroll.business_rules');
    Route::post('/admin/payroll/update', [PayrollSettingsController::class, 'updateSection'])->name('admin.payroll.update');
    Route::any('/admin/payroll/preview', [PayrollSettingsController::class, 'preview'])->name('admin.payroll.preview');
    Route::post('/admin/payroll/sync-holidays', [PayrollSettingsController::class, 'syncHolidays'])->name('admin.payroll.sync_holidays');
    Route::post('/admin/payroll/components', [PayrollSettingsController::class, 'saveComponents'])->name('admin.payroll.save_components');
    Route::post('/admin/payroll/employee-components', [\App\Http\Controllers\EmployeeSalaryComponentController::class, 'store'])->name('admin.payroll.employee_components.store');

    // Phase 6C Configuration UI
    Route::get('/payroll/configuration', [\App\Http\Controllers\PayrollConfigurationController::class, 'index'])->name('payroll.configuration.index');
    Route::post('/payroll/configuration', [\App\Http\Controllers\PayrollConfigurationController::class, 'store'])->name('payroll.configuration.store');
    Route::post('/payroll/configuration/{id}/approve', [\App\Http\Controllers\PayrollConfigurationController::class, 'approve'])->name('payroll.configuration.approve');
    Route::post('/payroll/configuration/{id}/reject', [\App\Http\Controllers\PayrollConfigurationController::class, 'reject'])->name('payroll.configuration.reject');

    // Employee Onboarding & Digital Document Management (V2.4)
    Route::get('/onboarding', [OnboardingController::class, 'dashboard'])->name('onboarding.dashboard');
    Route::get('/onboarding/wizard', [OnboardingController::class, 'wizard'])->name('onboarding.wizard');
    Route::post('/onboarding/store', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/onboarding/{id}/profile', [OnboardingController::class, 'profile'])->name('onboarding.profile');
    Route::post('/onboarding/{id}/security', [OnboardingController::class, 'updateSecurity'])->name('onboarding.security.update');
    Route::post('/onboarding/{id}/approve', [OnboardingController::class, 'approveStage'])->name('onboarding.approve');
    Route::match(['get', 'post'], '/onboarding/{id}/skip', [OnboardingController::class, 'skipOnboarding'])->name('onboarding.skip');
    Route::post('/onboarding/{id}/assets', [OnboardingController::class, 'assignAsset'])->name('onboarding.assign-asset');
    Route::post('/onboarding/{id}/assets/{assetId}/return', [OnboardingController::class, 'returnAsset'])->name('onboarding.return-asset');
    Route::post('/onboarding/{id}/agreements/generate', [OnboardingController::class, 'generateAgreement'])->name('onboarding.generate-agreement');
    Route::post('/onboarding/{id}/agreements/{agreementId}/sign', [OnboardingController::class, 'signAgreement'])->name('onboarding.sign-agreement');
    Route::get('/onboarding/agreements/{agreementId}/download', [OnboardingController::class, 'downloadAgreement'])->name('onboarding.download-agreement');
    Route::get('/onboarding/assets/{assetId}/handover', [OnboardingController::class, 'downloadAssetHandover'])->name('onboarding.download-asset-handover');

    // Agreement Templates CRUD
    Route::get('/onboarding/templates', [OnboardingController::class, 'templates'])->name('onboarding.templates');
    Route::post('/onboarding/templates', [OnboardingController::class, 'storeTemplate'])->name('onboarding.templates.store');
    Route::post('/onboarding/templates/{id}/update', [OnboardingController::class, 'updateTemplate'])->name('onboarding.templates.update');
    Route::post('/onboarding/templates/{id}/delete', [OnboardingController::class, 'destroyTemplate'])->name('onboarding.templates.delete');

    // Root Legacy Aliases for Administration & Governance Modules
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/history', [PermissionController::class, 'history'])->name('permissions.history');
    Route::get('/permissions/templates', [PermissionController::class, 'templatesIndex'])->name('permissions.templates');
    Route::post('/permissions/templates', [PermissionController::class, 'storeTemplate'])->name('permissions.templates.store');
    Route::post('/permissions/templates/{id}/delete', [PermissionController::class, 'destroyTemplate'])->name('permissions.templates.delete');
    Route::post('/permissions/bulk-assign', [PermissionController::class, 'bulkAssign'])->name('permissions.bulk-assign');
    Route::post('/permissions/{userId}', [PermissionController::class, 'storeUserPermissions'])->name('permissions.store');
    Route::post('/permissions/{userId}/apply-template', [PermissionController::class, 'applyTemplate'])->name('permissions.apply-template');
    Route::get('/access-levels', [AccessLevelController::class, 'index'])->name('access-levels.index');
    Route::get('/access-levels/{roleId}/matrix', [AccessLevelController::class, 'matrix'])->name('access-levels.matrix');
    Route::post('/access-levels/{roleId}/{levelId}', [AccessLevelController::class, 'store'])->name('access-levels.store');
    Route::get('/queue-manager', [QueueManagerController::class, 'index'])->name('queue-manager.index');
    Route::post('/queue-manager', [QueueManagerController::class, 'queueCommand'])->name('queue-manager.store');
    Route::post('/queue-manager/{id}/retry', [QueueManagerController::class, 'retryCommand'])->name('queue-manager.retry');
    Route::post('/queue-manager/{id}/cancel', [QueueManagerController::class, 'cancelCommand'])->name('queue-manager.cancel');
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::post('/menus/{id}/update', [MenuController::class, 'update'])->name('menus.update');
    Route::post('/menus/{id}/delete', [MenuController::class, 'destroy'])->name('menus.delete');
    Route::post('/menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
    Route::get('/approval-workflows', [ApprovalWorkflowController::class, 'index'])->name('approval-workflows.index');
    Route::post('/approval-workflows', [ApprovalWorkflowController::class, 'store'])->name('approval-workflows.store');
    Route::post('/approval-workflows/{id}/update', [ApprovalWorkflowController::class, 'update'])->name('approval-workflows.update');
    Route::post('/approval-workflows/{id}/delete', [ApprovalWorkflowController::class, 'destroy'])->name('approval-workflows.delete');
    Route::post('/approval-workflows/{id}/steps', [ApprovalWorkflowController::class, 'addStep'])->name('approval-workflows.add-step');
    Route::post('/approval-workflows/steps/{stepId}/delete', [ApprovalWorkflowController::class, 'deleteStep'])->name('approval-workflows.delete-step');
});



// Fallback Route for Serving Storage Assets
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');
