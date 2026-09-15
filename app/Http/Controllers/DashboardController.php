<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Backup;
use App\Models\Company;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceEventLog;
use App\Models\Employee;
use App\Models\ManualLog;
use App\Models\User;
use App\Models\UserPermissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today()->toDateString();

        // 1. Attendance Metrics (Powered by New Engine)
        $totalEmployees = Employee::where('status', 'Active')->count();
        
        $presentToday = \App\Models\DailyAttendanceSummary::where('attendance_date', $today)
            ->where('status', 'PRESENT')
            ->count();

        $lateToday = \App\Models\DailyAttendanceSummary::where('attendance_date', $today)
            ->where('is_late_in', true)
            ->count();

        $earlyOutToday = \App\Models\DailyAttendanceSummary::where('attendance_date', $today)
            ->where('is_early_out', true)
            ->count();

        $overtimeToday = \App\Models\DailyAttendanceSummary::where('attendance_date', $today)
            ->where('is_ot_eligible', true)
            ->count();

        $absentToday = \App\Models\DailyAttendanceSummary::where('attendance_date', $today)
            ->where('status', 'ABSENT')
            ->count();
            
        if ($absentToday === 0) {
            // Fallback if engine hasn't fully processed all absentees yet for today
            $absentToday = max(0, $totalEmployees - $presentToday);
        }

        // 2. Pending Corrections
        $pendingCorrections = ManualLog::where('status', 'Pending')->count();

        // 3. Device Metrics
        $devices = Device::all();
        $onlineDevices = 0;
        $offlineDevices = 0;
        $pendingDevices = 0;

        foreach ($devices as $d) {
            if ($d->status === 'Pending Approval') {
                $pendingDevices++;
            } elseif ($d->status === 'Disabled') {
                $offlineDevices++;
            } else {
                if ($d->is_online) {
                    $onlineDevices++;
                } else {
                    $offlineDevices++;
                }
            }
        }

        // 4. System Health Panel
        $dbStatus = 'Connected';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'Error';
        }

        $lastBackup = Backup::orderBy('id', 'desc')->first();
        $todayLogsCount = AttendanceLog::where('attendance_date', $today)->count();
        $unprocessedLogs = AttendanceLog::whereNull('employee_id')->count();

        // 5. Last logs/communication
        $lastLog = AttendanceLog::orderBy('id', 'desc')->first();
        $lastComm = DeviceEventLog::orderBy('id', 'desc')->first();

        // 6. Multi-Company & Department Summaries
        $companies = Company::all();
        $departments = Department::all();

        // 7. Security & Overrides Telemetry
        $usersWithCustomPermsCount = User::whereHas('directPermissions')->count();
        $adminUsersCount = User::whereIn('role', ['Super Administrator', 'HR Administrator'])->count();
        $permissionChangesToday = UserPermissionHistory::whereDate('created_at', Carbon::today())->count();
        $lockedAccountsCount = User::where('status', 'Inactive')->count();

        return view('dashboard', compact(
            'presentToday',
            'absentToday',
            'lateToday',
            'earlyOutToday',
            'overtimeToday',
            'pendingCorrections',
            'onlineDevices',
            'offlineDevices',
            'pendingDevices',
            'dbStatus',
            'lastBackup',
            'todayLogsCount',
            'unprocessedLogs',
            'lastLog',
            'lastComm',
            'companies',
            'departments',
            'today',
            'usersWithCustomPermsCount',
            'adminUsersCount',
            'permissionChangesToday',
            'lockedAccountsCount'
        ));
    }
}
