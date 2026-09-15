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
use App\Services\NetworkScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class AttendanceController extends Controller
{
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















    /**
     * Devices CRUD & Actions
     */









    /**
     * Enterprise Attendance Operations Center
     */
    public function attendanceLogs(Request $request)
    {
        $query = AttendanceLog::with('employee.company', 'employee.branch', 'employee.department', 'device');

        // Search Employee
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('company_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('company_id', $request->input('company_id'));
            });
        }
        if ($request->filled('branch_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('branch_id', $request->input('branch_id'));
            });
        }
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->input('department_id'));
            });
        }
        if ($request->filled('status')) {
            $query->where('attendance_status', $request->input('status'));
        }
        if ($request->filled('verification_method')) {
            $query->where('verification_method', $request->input('verification_method'));
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('attendance_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        $logs = $query->orderBy('attendance_timestamp', 'desc')->paginate(30);

        // Operational Telemetry Metrics
        $todayStr = Carbon::today()->toDateString();
        $totalEmployeesCount = Employee::where('status', 'Active')->count();
        $presentTodayCount = AttendanceLog::where('attendance_date', $todayStr)->distinct('employee_id')->count('employee_id');

        $metrics = [
            'today_punches' => AttendanceLog::where('attendance_date', $todayStr)->count(),
            'employees_present' => $presentTodayCount,
            'employees_absent' => max(0, $totalEmployeesCount - $presentTodayCount),
            'late_arrivals' => AttendanceLog::where('attendance_date', $todayStr)->where('attendance_status', 'Late')->count(),
            'early_departures' => AttendanceLog::where('attendance_date', $todayStr)->where('attendance_status', 'Early Out')->count(),
            'missing_punches' => AttendanceLog::where('attendance_date', $todayStr)->whereNull('attendance_type')->count(),
            'devices_online' => Device::where('status', 'Online')->count(),
            'devices_offline' => Device::where('status', 'Offline')->count(),
        ];

        $companies = Company::where('status', 'Active')->get();
        $branches = Branch::all();
        $departments = Department::all();
        $shifts = Shift::all();
        $devices = Device::all();

        return view('attendance.index', compact('logs', 'metrics', 'companies', 'branches', 'departments', 'shifts', 'devices'));
    }

    public function clearAttendanceLogs(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $deleted = AttendanceLog::whereBetween('attendance_date', [$startDate, $endDate])->delete();
            $msg = "Cleared {$deleted} punch logs between {$startDate} and {$endDate}.";
        } else {
            $deleted = AttendanceLog::query()->delete();
            $msg = "Cleared all {$deleted} raw attendance punch logs from the system.";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CLEAR_ATTENDANCE_LOGS',
            'module' => 'Attendance Management',
            'record_id' => null,
            'new_value' => json_encode(['deleted_count' => $deleted, 'start_date' => $startDate, 'end_date' => $endDate]),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', $msg);
    }

    public function recalculateAttendance()
    {
        @set_time_limit(300); // Allow 5 minutes for large datasets
        @ini_set('memory_limit', '512M');
        
        $updated = 0;

        AttendanceLog::with('employee.shift')->chunkById(500, function ($logs) use (&$updated) {
            foreach ($logs as $log) {
                if (!$log->attendance_time) continue;

                $shift = $log->employee?->shift;
                $status = 'Present';

                if ($shift && $shift->start_time) {
                    try {
                        $punchTime = Carbon::parse($log->attendance_time)->format('H:i:s');
                        $graceMinutes = $shift->late_grace_period ?? 15;
                        $startWithGrace = Carbon::parse($shift->start_time)->addMinutes($graceMinutes)->format('H:i:s');

                        if ($punchTime > $startWithGrace) {
                            $status = 'Late';
                        }
                    } catch (\Exception $e) {
                        continue; // Skip logs with corrupt timestamp formats
                    }
                }

                // Only perform database update if status actually changed to save execution time
                if ($log->attendance_status !== $status) {
                    // Use DB facade to explicitly include attendance_timestamp. 
                    // This prevents older MySQL versions from firing "ON UPDATE CURRENT_TIMESTAMP"
                    // which causes unique constraint violations (Error 1062) by grouping times together.
                    \Illuminate\Support\Facades\DB::table('attendance_logs')
                        ->where('id', $log->id)
                        ->update([
                            'attendance_status' => $status,
                            'attendance_timestamp' => $log->attendance_timestamp
                        ]);
                    $updated++;
                }
            }
        });

        return back()->with('success', "Recalculated attendance statuses. Updated {$updated} punch log records.");
    }

    /**
     * Manual Logs (Correction requests)
     */
    public function manualLogs()
    {
        $corrections = ManualLog::with('employee.company', 'approvedBy')->orderBy('id', 'desc')->get();
        $employees = Employee::where('status', 'Active')->get();

        return view('corrections.index', compact('corrections', 'employees'));
    }

    public function storeManualLog(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'request_type' => 'required|string',
            'request_date' => 'required|date',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'reason' => 'required|string',
        ]);

        $log = ManualLog::create([
            'employee_id' => $request->input('employee_id'),
            'request_type' => $request->input('request_type'),
            'request_date' => $request->input('request_date'),
            'check_in' => $request->input('check_in'),
            'check_out' => $request->input('check_out'),
            'reason' => $request->input('reason'),
            'status' => 'Pending',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'SUBMIT_CORRECTION_REQUEST',
            'module' => 'Manual Logs',
            'record_id' => $log->id,
            'new_value' => json_encode($log),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Correction request submitted!');
    }

    public function approveManualLog(Request $request, $id)
    {
        $log = ManualLog::findOrFail($id);
        $oldVal = json_encode($log);

        $log->status = 'Approved';
        $log->approved_by = Auth::id();
        $log->approved_at = now();
        $log->remarks = $request->input('remarks');
        $log->save();

        // Inject the correction into the attendance logs!
        // Check-In
        if ($log->check_in) {
            $ts = $log->request_date->toDateString() . ' ' . $log->check_in;
            $exists = AttendanceLog::where('employee_id', $log->employee_id)
                ->where('attendance_timestamp', $ts)
                ->exists();
            if (!$exists) {
                AttendanceLog::create([
                    'employee_id' => $log->employee_id,
                    'device_id' => null,
                    'attendance_date' => $log->request_date->toDateString(),
                    'attendance_time' => $log->check_in,
                    'attendance_timestamp' => $ts,
                    'verification_method' => 'Manual Override',
                    'verify_code' => '99',
                    'device_serial' => 'Manual Entry',
                    'source' => 'Manual',
                    'attendance_status' => 'Present',
                    'attendance_type' => 'Check-In',
                    'raw_data' => 'MANUAL_CORRECTION_APPROVED_BY_' . Auth::user()->username,
                ]);
            }
        }

        // Check-Out
        if ($log->check_out) {
            $ts = $log->request_date->toDateString() . ' ' . $log->check_out;
            $exists = AttendanceLog::where('employee_id', $log->employee_id)
                ->where('attendance_timestamp', $ts)
                ->exists();
            if (!$exists) {
                AttendanceLog::create([
                    'employee_id' => $log->employee_id,
                    'device_id' => null,
                    'attendance_date' => $log->request_date->toDateString(),
                    'attendance_time' => $log->check_out,
                    'attendance_timestamp' => $ts,
                    'verification_method' => 'Manual Override',
                    'verify_code' => '99',
                    'device_serial' => 'Manual Entry',
                    'source' => 'Manual',
                    'attendance_status' => 'Present',
                    'attendance_type' => 'Check-Out',
                    'raw_data' => 'MANUAL_CORRECTION_APPROVED_BY_' . Auth::user()->username,
                ]);
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'APPROVE_CORRECTION_REQUEST',
            'module' => 'Manual Logs',
            'record_id' => $log->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($log),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Correction request approved, logs inserted!');
    }

    public function rejectManualLog(Request $request, $id)
    {
        $log = ManualLog::findOrFail($id);
        $oldVal = json_encode($log);

        $log->status = 'Rejected';
        $log->approved_by = Auth::id();
        $log->approved_at = now();
        $log->remarks = $request->input('remarks');
        $log->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'REJECT_CORRECTION_REQUEST',
            'module' => 'Manual Logs',
            'record_id' => $log->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($log),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Correction request rejected.');
    }

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
