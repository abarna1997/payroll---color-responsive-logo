<?php

namespace App\Services\Reporting;

use App\Models\AttendanceLog;
use App\Models\DeviceEventLog;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use stdClass;

class AttendanceReportService
{
    /**
     * Process raw attendance logs and aggregate them into daily paired records.
     */
    public function processDailyAttendance($rawLogs, $startDate, $endDate, $companyId = null, $branchId = null, $departmentId = null)
    {
        // 1. Group logs by employee and date
        $grouped = $rawLogs->groupBy(function ($log) {
            return $log->employee_id . '_' . $log->attendance_date->toDateString();
        });

        // 2. Resolve employees with Eager loaded relations
        $employeeIds = $rawLogs->pluck('employee_id')->filter()->unique();
        $employees = Employee::with(['company', 'branch', 'department', 'shift', 'secondaryShift'])
            ->whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');

        $aggregated = [];

        foreach ($grouped as $key => $logs) {
            list($empId, $dateStr) = explode('_', $key);
            $employee = $employees->get($empId);

            if (!$employee) {
                continue;
            }

            // Group logs by check-in and check-out
            $checkInLogs = $logs->filter(function ($l) {
                return $l->attendance_type === 'Check-In';
            })->sortBy('attendance_timestamp');

            $checkOutLogs = $logs->filter(function ($l) {
                return $l->attendance_type === 'Check-Out';
            })->sortByDesc('attendance_timestamp');

            $firstCheckIn = $checkInLogs->first();
            $lastCheckOut = $checkOutLogs->first();

            $row = new stdClass();
            $row->employee_id = $employee->employee_id;
            $row->employee_name = $employee->full_name;
            $row->company = $employee->company ? $employee->company->company_name : 'N/A';
            $row->branch = $employee->branch ? $employee->branch->branch_name : 'N/A';
            $row->department = $employee->department ? $employee->department->department_name : 'N/A';
            $row->date = $dateStr;
            $row->device = $firstCheckIn ? ($firstCheckIn->device ? $firstCheckIn->device->device_name : 'N/A') : ($lastCheckOut && $lastCheckOut->device ? $lastCheckOut->device->device_name : 'N/A');

            $checkInTime = $firstCheckIn ? Carbon::parse($firstCheckIn->attendance_time) : null;
            $checkOutTime = $lastCheckOut ? Carbon::parse($lastCheckOut->attendance_time) : null;

            $row->check_in_time = $firstCheckIn ? $checkInTime->format('H:i:s') : '—';
            $row->check_out_time = $lastCheckOut ? $checkOutTime->format('H:i:s') : '—';

            $row->working_hours = '0.00 hrs';
            $row->late_minutes = 0;
            $row->early_out_minutes = 0;
            $row->overtime = '0.00 hrs';

            $checkInStatus = '—';
            $checkOutStatus = '—';
            $finalStatus = 'Absent';

            if ($firstCheckIn || $lastCheckOut) {
                $punchTimeStr = $firstCheckIn ? $firstCheckIn->attendance_time : ($lastCheckOut ? $lastCheckOut->attendance_time : null);
                $shift = $employee->getMatchingShift($punchTimeStr);

                if ($shift) {
                    $shiftStart = Carbon::parse($shift->start_time);
                    $shiftEnd = Carbon::parse($shift->end_time);

                    // Check-in status
                    if ($firstCheckIn) {
                        $lateThreshold = $shiftStart->copy()->addMinutes(15);
                        $absentThreshold = $shiftStart->copy()->addMinutes(60);

                        if ($checkInTime->greaterThanOrEqualTo($absentThreshold)) {
                            $checkInStatus = 'Absent';
                        } elseif ($checkInTime->greaterThanOrEqualTo($lateThreshold)) {
                            $checkInStatus = 'Late';
                            $row->late_minutes = $checkInTime->diffInMinutes($shiftStart);
                        } else {
                            $checkInStatus = 'On Time';
                        }
                    } else {
                        $checkInStatus = 'Missing In';
                    }

                    // Check-out status
                    if ($lastCheckOut) {
                        $earlyOutLimit = $shiftEnd->copy()->subMinutes(120);
                        $normalOutLimit = $shiftEnd;

                        if ($checkOutTime->lessThan($earlyOutLimit)) {
                            $checkOutStatus = 'Invalid Out';
                        } elseif ($checkOutTime->lessThan($normalOutLimit)) {
                            $checkOutStatus = 'Early Out';
                            $row->early_out_minutes = $shiftEnd->diffInMinutes($checkOutTime);
                        } else {
                            $otLimit = $shiftEnd->copy()->addMinutes(30);
                            if ($checkOutTime->greaterThanOrEqualTo($otLimit)) {
                                $checkOutStatus = 'Overtime';
                                $otDiff = $checkOutTime->diffInMinutes($shiftEnd);
                                $row->overtime = round($otDiff / 60, 2) . ' hrs';
                            } else {
                                $checkOutStatus = 'Normal Out';
                            }
                        }
                    } else {
                        $checkOutStatus = 'Missing Out';
                    }

                    // Calculate Working Hours
                    if ($firstCheckIn && $lastCheckOut) {
                        $workingDiff = $checkOutTime->diffInMinutes($checkInTime);
                        $row->working_hours = round($workingDiff / 60, 2) . ' hrs';
                    }

                    // Final Status determination
                    if ($checkInStatus === 'Absent' || $checkOutStatus === 'Invalid Out') {
                        $finalStatus = 'Absent';
                    } elseif ($checkInStatus === 'Missing In') {
                        $finalStatus = 'Missing In';
                    } elseif ($checkOutStatus === 'Missing Out') {
                        $finalStatus = 'Missing Out';
                    } elseif ($checkInStatus === 'Late' && $checkOutStatus === 'Early Out') {
                        $finalStatus = 'Late + Early Out';
                    } elseif ($checkInStatus === 'Late') {
                        $finalStatus = 'Late';
                    } elseif ($checkOutStatus === 'Early Out') {
                        $finalStatus = 'Early Out';
                    } elseif ($checkOutStatus === 'Overtime') {
                        $finalStatus = 'Overtime';
                    } else {
                        $finalStatus = 'Present';
                    }
                }
            }

            $row->check_in_status = $checkInStatus;
            $row->check_out_status = $checkOutStatus;
            $row->final_status = $finalStatus;

            $aggregated[] = $row;
        }

        return $aggregated;
    }
}
