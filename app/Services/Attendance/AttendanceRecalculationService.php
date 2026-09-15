<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LateArrival;
use App\Models\EarlyDeparture;
use App\Models\Overtime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceRecalculationService
{
    /**
     * Recalculate raw attendance log statuses based on shift rules.
     */
    public function recalculateLogs(array $filters, string $ipAddress): int
    {
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $reportType = $filters['report_type'];
        $companyId = $filters['company_id'] ?? null;
        $branchId = $filters['branch_id'] ?? null;
        $departmentId = $filters['department_id'] ?? null;

        $sDate = ($reportType === 'monthly') ? Carbon::parse($startDate)->startOfMonth()->toDateString() : $startDate;
        $eDate = ($reportType === 'monthly') ? Carbon::parse($startDate)->endOfMonth()->toDateString() : $endDate;

        $recalcQuery = AttendanceLog::whereBetween('attendance_date', [$sDate, $eDate]);

        // Apply filters to target only specific employees
        if ($companyId || $branchId || $departmentId) {
            $recalcQuery->whereHas('employee', function ($q) use ($companyId, $branchId, $departmentId) {
                if ($companyId) {
                    $q->where('company_id', $companyId);
                }
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
            });
        }

        $logsToRecalc = $recalcQuery->whereNotNull('employee_id')->get();
        $recalcCount = 0;

        foreach ($logsToRecalc as $log) {
            $employee = Employee::find($log->employee_id);
            if ($employee && ($employee->shift_id || $employee->secondary_shift_id)) {
                $shift = $employee->getMatchingShift($log->attendance_time);

                $shiftStart = Carbon::parse($shift->start_time);
                $shiftEnd = Carbon::parse($shift->end_time);

                $punchTime = Carbon::parse($log->attendance_time);
                $status = 'Present';

                if ($log->attendance_type === 'Check-In') {
                    $lateThreshold = $shiftStart->copy()->addMinutes(15);
                    $absentThreshold = $shiftStart->copy()->addMinutes(60);

                    if ($punchTime->greaterThanOrEqualTo($absentThreshold)) {
                        $status = 'Absent';
                    } elseif ($punchTime->greaterThanOrEqualTo($lateThreshold)) {
                        $status = 'Late';
                        if (\App\Models\Setting::getVal('System', 'AutoFetchExceptions') === 'true') {
                            LateArrival::firstOrCreate(
                                ['employee_id' => $employee->id, 'date' => $punchTime->toDateString()],
                                [
                                    'minutes_late' => $punchTime->diffInMinutes($shiftStart),
                                    'status' => 'Pending',
                                    'reason' => 'Auto-generated from device punch'
                                ]
                            );
                        }
                    } else {
                        $status = 'Present';
                    }
                } elseif ($log->attendance_type === 'Check-Out') {
                    $earlyOutLimit = $shiftEnd->copy()->subMinutes(120);
                    $normalOutLimit = $shiftEnd;

                    if ($punchTime->lessThan($earlyOutLimit)) {
                        $status = 'Invalid Out';
                    } elseif ($punchTime->lessThan($normalOutLimit)) {
                        $status = 'Early Out';
                        if (\App\Models\Setting::getVal('System', 'AutoFetchExceptions') === 'true') {
                            EarlyDeparture::firstOrCreate(
                                ['employee_id' => $employee->id, 'date' => $punchTime->toDateString()],
                                [
                                    'minutes_early' => $shiftEnd->diffInMinutes($punchTime),
                                    'status' => 'Pending',
                                    'reason' => 'Auto-generated from device punch'
                                ]
                            );
                        }
                    } else {
                        $otLimit = $shiftEnd->copy()->addMinutes(30);
                        if ($punchTime->greaterThanOrEqualTo($otLimit)) {
                            $status = 'Overtime';
                            if (\App\Models\Setting::getVal('System', 'AutoFetchExceptions') === 'true') {
                                Overtime::firstOrCreate(
                                    ['employee_id' => $employee->id, 'overtime_date' => $punchTime->toDateString()],
                                    [
                                        'hours' => round($punchTime->diffInMinutes($shiftEnd) / 60, 2),
                                        'status' => 'Pending',
                                        'reason' => 'Auto-generated from device punch'
                                    ]
                                );
                            }
                        } else {
                            $status = 'Present';
                        }
                    }
                }

                if ($log->attendance_status !== $status) {
                    $log->attendance_status = $status;
                    $log->save();
                    $recalcCount++;
                }
            }
        }

        // Write an Audit Log for recalculation
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'RECALCULATE_ATTENDANCE_LOGS',
            'module' => 'Attendance Reports',
            'record_id' => 0,
            'old_value' => json_encode(['start_date' => $sDate, 'end_date' => $eDate, 'company_id' => $companyId]),
            'new_value' => json_encode(['recalculated_count' => $recalcCount]),
            'ip_address' => $ipAddress,
        ]);

        return $recalcCount;
    }
}
