<?php

namespace App\Services\Management;

use App\Models\AttendanceLog;
use Illuminate\Support\Carbon;

class AttendanceRecalculationService
{
    public function recalculateAttendance()
    {
        $logs = AttendanceLog::with('employee.shift')->get();
        $updated = 0;

        foreach ($logs as $log) {
            $shift = $log->employee->shift ?? null;
            $status = 'Present';

            if ($shift && $shift->start_time) {
                $punchTime = Carbon::parse($log->attendance_time)->format('H:i:s');
                $shiftStart = Carbon::parse($shift->start_time)->format('H:i:s');
                $graceMinutes = $shift->late_grace_period ?? 15;

                $startWithGrace = Carbon::parse($shift->start_time)->addMinutes($graceMinutes)->format('H:i:s');

                if ($punchTime > $startWithGrace) {
                    $status = 'Late';
                }
            }

            $log->update(['attendance_status' => $status]);
            $updated++;
        }

        return $updated;
    }
}
