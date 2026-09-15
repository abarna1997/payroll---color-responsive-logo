<?php

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\LeaveRequest;

class PayrollAttendanceService
{
    protected $preloadedSummaries = null;
    protected $preloadedLeaves = null;
    protected $currentPreloadPeriodId = null;

    /**
     * Preload all attendance and leave data for the entire payroll period in bulk.
     * This avoids N+1 queries during bulk payroll generation.
     */
    public function preloadForPeriod(PayrollPeriod $period): void
    {
        $this->currentPreloadPeriodId = $period->id;
        
        // Load all DailyAttendanceSummary for this period, grouped by employee_id
        $summaries = DailyAttendanceSummary::whereBetween('attendance_date', [
            $period->start_date->startOfDay(), 
            $period->end_date->endOfDay()
        ])->get();
        $this->preloadedSummaries = $summaries->groupBy('employee_id');

        // Load all approved LeaveRequests overlapping with this period, grouped by employee_id
        $leaves = LeaveRequest::with('leaveType')
            ->where('status', 'Approved')
            ->where(function($query) use ($period) {
                $query->whereBetween('start_date', [
                          $period->start_date->startOfDay(), 
                          $period->end_date->endOfDay()
                      ])
                      ->orWhereBetween('end_date', [
                          $period->start_date->startOfDay(), 
                          $period->end_date->endOfDay()
                      ])
                      ->orWhere(function($q) use ($period) {
                          $q->where('start_date', '<=', $period->start_date->startOfDay())
                            ->where('end_date', '>=', $period->end_date->endOfDay());
                      });
            })->get();
        $this->preloadedLeaves = $leaves->groupBy('employee_id');
    }

    /**
     * Get aggregated attendance data for payroll calculation from the central Attendance Engine.
     */
    public function getAttendanceForPeriod(Employee $employee, PayrollPeriod $period): array
    {
        // Use preloaded data if available for this specific period
        if ($this->currentPreloadPeriodId === $period->id && $this->preloadedSummaries !== null) {
            $summaries = $this->preloadedSummaries->get($employee->id, collect());
        } else {
            $summaries = DailyAttendanceSummary::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [
                    $period->start_date->startOfDay(), 
                    $period->end_date->endOfDay()
                ])
                ->get();
        }

        if ($summaries->isEmpty()) {
            throw new \Exception("Attendance has not been processed for this payroll period.");
        }

        $presentDays = 0;
        $absentDays = 0;
        $halfDays = 0;
        $lateMinutes = 0;
        $earlyOutMinutes = 0;
        $overtimeMinutes = 0;
        $normalOvertimeMinutes = 0;
        $holidayOvertimeMinutes = 0;
        $offDayOvertimeMinutes = 0;
        $weekendOvertimeMinutes = 0;
        $workingMinutes = 0;
        $unpaidLeaveDays = 0;
        $paidLeaveDays = 0;

        foreach ($summaries as $summary) {
            $lateMinutes += $summary->late_minutes;
            $earlyOutMinutes += $summary->early_out_minutes;
            $overtimeMinutes += $summary->overtime_minutes;
            $workingMinutes += $summary->working_minutes;

            if ($summary->overtime_minutes > 0) {
                switch (strtoupper($summary->status)) {
                    case 'HOLIDAY':
                        $holidayOvertimeMinutes += $summary->overtime_minutes;
                        break;
                    case 'OFF_DAY':
                    case 'OFF DAY':
                        $offDayOvertimeMinutes += $summary->overtime_minutes;
                        break;
                    case 'WEEKEND':
                        $weekendOvertimeMinutes += $summary->overtime_minutes;
                        break;
                    default:
                        $normalOvertimeMinutes += $summary->overtime_minutes;
                        break;
                }
            }

            switch ($summary->status) {
                case 'PRESENT':
                case 'LATE':
                case 'EARLY_OUT':
                case 'OVERTIME':
                    $presentDays++;
                    break;
                case 'ABSENT':
                    $absentDays++;
                    break;
                case 'HALF_DAY':
                case 'FIRST_HALF':
                case 'SECOND_HALF':
                    $halfDays++;
                    break;
                case 'LEAVE':
                    if ($this->currentPreloadPeriodId === $period->id && $this->preloadedLeaves !== null) {
                        $employeeLeaves = $this->preloadedLeaves->get($employee->id, collect());
                        $leaveRequest = $employeeLeaves->first(function($leave) use ($summary) {
                            return $leave->start_date <= $summary->attendance_date->toDateString() && $leave->end_date >= $summary->attendance_date->toDateString();
                        });
                    } else {
                        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
                            ->where('status', 'Approved')
                            ->where('start_date', '<=', $summary->attendance_date->toDateString())
                            ->where('end_date', '>=', $summary->attendance_date->toDateString())
                            ->first();
                    }
                    
                    if ($leaveRequest && $leaveRequest->leaveType) {
                        if ($leaveRequest->leaveType->is_paid) {
                            $paidLeaveDays++;
                        } else {
                            $unpaidLeaveDays++;
                        }
                    } else {
                        // Default to paid if unknown
                        $paidLeaveDays++;
                    }
                    break;
                case 'HOLIDAY':
                case 'OFF_DAY':
                case 'WEEKEND':
                case 'PENDING':
                case 'INCOMPLETE':
                default:
                    // Does not directly count as absent or present in the raw totals unless worked
                    if ($summary->working_minutes > 0) {
                        $presentDays++;
                    }
                    break;
            }
        }

        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'half_days' => $halfDays,
            'paid_leaves' => $paidLeaveDays,
            'unpaid_leaves' => $unpaidLeaveDays,
            'late_minutes' => $lateMinutes,
            'early_out_minutes' => $earlyOutMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'normal_overtime_minutes' => $normalOvertimeMinutes,
            'holiday_overtime_minutes' => $holidayOvertimeMinutes,
            'off_day_overtime_minutes' => $offDayOvertimeMinutes,
            'weekend_overtime_minutes' => $weekendOvertimeMinutes,
            'working_minutes' => $workingMinutes,
            
            // Map legacy V and H metrics directly
            'v_days' => $absentDays + $unpaidLeaveDays,
            'h_days' => $halfDays,
        ];
    }
}
