<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\DailyAttendanceSummary;
use App\Models\Employee;
use App\Models\Setting;
use Carbon\Carbon;

class AttendanceEngineService
{
    public function processRawLog(AttendanceLog $log)
    {
        $employee = Employee::find($log->employee_id);
        if (!$employee) {
            return false;
        }

        $date = Carbon::parse($log->attendance_timestamp)->format('Y-m-d');
        
        // Check for approved leave
        $isLeave = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
        
        $summary = DailyAttendanceSummary::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => $date
            ],
            [
                'shift_id' => null, // Should resolve shift dynamically
                'is_wfh' => false,
                'is_leave' => $isLeave,
                'status' => $isLeave ? 'Leave' : 'Present'
            ]
        );

        $punchTime = Carbon::parse($log->attendance_timestamp);
        
        // Very basic Check IN / OUT logic for demonstration
        if (!$summary->check_in || $punchTime->lt($summary->check_in)) {
            $summary->check_in = $punchTime;
        } elseif (!$summary->check_out || $punchTime->gt($summary->check_out)) {
            $summary->check_out = $punchTime;
        }
        
        $summary->save();
        
        return $summary;
    }
}
