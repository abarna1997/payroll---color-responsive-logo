<?php

namespace App\Services\Attendance;

use App\Models\Employee;
use App\Models\WfhRequest;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class WebPunchEligibilityService
{
    /**
     * Determine if the given employee can perform a Web Punch on the given date.
     *
     * @param Employee $employee
     * @param string|null $date
     * @return array
     */
    public function canWebPunch(Employee $employee, string $date = null): array
    {
        $date = $date ?? Carbon::today()->toDateString();

        if ($employee->status !== 'Active' && $employee->employment_status !== 'Active') {
            return [
                'eligible' => false,
                'reason' => 'EMPLOYEE_INACTIVE',
                'message' => 'Employee profile is not active.',
            ];
        }

        if (!$employee->shift_id) {
            return [
                'eligible' => false,
                'reason' => 'NO_SHIFT',
                'message' => 'No shift is assigned to this employee.',
            ];
        }

        if (!$employee->allow_remote_punch) {
            return [
                'eligible' => false,
                'reason' => 'REMOTE_PUNCH_DISABLED',
                'message' => 'Remote punch is disabled for this employee.',
            ];
        }

        $wfhRequest = WfhRequest::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        // Permanent WFH or HYBRID employees don't need a daily WFH request
        if (!$wfhRequest && !in_array($employee->work_mode, ['WFH', 'HYBRID'])) {
            return [
                'eligible' => false,
                'reason' => 'NO_WFH_REQUEST',
                'message' => 'No WFH request exists for today.',
            ];
        }

        if ($wfhRequest) {
            if ($wfhRequest->status === 'PENDING') {
                return [
                    'eligible' => false,
                    'reason' => 'WFH_PENDING',
                    'message' => 'Your WFH request is pending approval.',
                ];
            }

            if ($wfhRequest->status === 'REJECTED') {
                return [
                    'eligible' => false,
                    'reason' => 'WFH_REJECTED',
                    'message' => 'Your WFH request was rejected.',
                ];
            }

            if ($wfhRequest->status === 'CANCELLED') {
                return [
                    'eligible' => false,
                    'reason' => 'WFH_CANCELLED',
                    'message' => 'Your WFH request was cancelled.',
                ];
            }

            if ($wfhRequest->status !== 'APPROVED') {
                return [
                    'eligible' => false,
                    'reason' => 'WFH_NOT_APPROVED',
                    'message' => 'Your WFH request is not approved.',
                ];
            }

            if ($wfhRequest->shift_id && $wfhRequest->shift_id !== $employee->shift_id && $wfhRequest->shift_id !== $employee->secondary_shift_id) {
                return [
                    'eligible' => false,
                    'reason' => 'WFH_SHIFT_MISMATCH',
                    'message' => 'The approved WFH request shift does not match your assigned shift.',
                ];
            }
        }

        if (is_null($employee->home_latitude) || is_null($employee->home_longitude)) {
            return [
                'eligible' => false,
                'reason' => 'NO_HOME_LOCATION',
                'message' => 'Your WFH home location coordinates are not configured.',
            ];
        }

        // State Machine check (Check-In vs Check-Out)
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->where('attendance_date', $date)
            ->orderBy('attendance_time', 'asc')
            ->get();

        $hasCheckIn = false;
        $hasCheckOut = false;

        foreach ($logs as $log) {
            if ($log->attendance_type === 'Check-In') {
                $hasCheckIn = true;
            }
            if ($log->attendance_type === 'Check-Out') {
                $hasCheckOut = true;
            }
        }

        if ($hasCheckIn && $hasCheckOut) {
             return [
                'eligible' => false,
                'reason' => 'ALREADY_CHECKED_OUT',
                'message' => 'You have already checked out for today.',
            ];
        }

        return [
            'eligible' => true,
            'reason' => $wfhRequest ? 'APPROVED_WFH' : 'PERMANENT_REMOTE_OR_HYBRID',
            'work_mode' => $employee->work_mode,
            'shift_id' => $employee->shift_id,
            'wfh_request_id' => $wfhRequest ? $wfhRequest->id : null,
            'next_action' => $hasCheckIn ? 'CHECK_OUT' : 'CHECK_IN'
        ];
    }
}
