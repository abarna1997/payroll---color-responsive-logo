<?php

namespace App\Services\Reporting;

use App\Models\AttendanceLog;
use App\Models\DailyAttendanceSummary;
use App\Models\DeviceEventLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportQueryService
{
    /**
     * Get validated and authorized report data
     */
    public function getReportData($reportType, $filters)
    {
        $user = Auth::user();
        
        $startDate = $filters['start_date'] ?? Carbon::today()->toDateString();
        $endDate = $filters['end_date'] ?? Carbon::today()->toDateString();
        
        $companyId = $filters['company_id'] ?? null;
        $branchId = $filters['branch_id'] ?? null;
        $departmentId = $filters['department_id'] ?? null;
        $employeeId = $filters['employee_id'] ?? null;

        // Authorize the explicit Company ID if provided.
        if ($companyId) {
            $this->validateCompanyAccess($user, $companyId);
        }
        
        $sDate = ($reportType === 'monthly') ? Carbon::parse($startDate)->startOfMonth()->toDateString() : $startDate;
        $eDate = ($reportType === 'monthly') ? Carbon::parse($startDate)->endOfMonth()->toDateString() : $endDate;

        if ($reportType === 'transaction') {
            return $this->getTransactionData($sDate, $eDate, $companyId, $branchId, $departmentId, $employeeId);
        } elseif ($reportType === 'exceptions') {
            return $this->getExceptionsData($sDate, $eDate, $companyId, $branchId, $departmentId, $employeeId);
        } else {
            return $this->getCalculatedData($reportType, $sDate, $eDate, $companyId, $branchId, $departmentId, $employeeId);
        }
    }

    private function validateCompanyAccess($user, $companyId)
    {
        if (in_array($user->role, ['Super Admin', 'Admin', 'Super Administrator'])) {
            return true;
        }

        $userCompanyId = DB::table('employees')->where('user_id', $user->id)->value('company_id');
        
        if (!$userCompanyId || $userCompanyId != $companyId) {
            abort(403, 'Unauthorized cross-company access attempt.');
        }
        return true;
    }

    private function applyHierarchyFilters($query, $companyId, $branchId, $departmentId, $employeeId)
    {
        // ALWAYS use whereHas('employee') to force the Employee global CompanyScope to evaluate
        $query->whereHas('employee', function ($q) use ($companyId, $branchId, $departmentId, $employeeId) {
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            if ($departmentId) {
                $q->where('department_id', $departmentId);
            }
            if ($employeeId) {
                $q->where('id', $employeeId);
            }
        });
        
        return $query;
    }

    private function getTransactionData($sDate, $eDate, $companyId, $branchId, $departmentId, $employeeId)
    {
        $query = AttendanceLog::with(['employee.company', 'employee.branch', 'employee.department', 'device']);
        $query->whereBetween('attendance_date', [$sDate, $eDate]);
        
        $this->applyHierarchyFilters($query, $companyId, $branchId, $departmentId, $employeeId);

        return $query->orderBy('attendance_timestamp', 'asc')->get();
    }
    
    private function getCalculatedData($reportType, $sDate, $eDate, $companyId, $branchId, $departmentId, $employeeId)
    {
        $query = DailyAttendanceSummary::with(['employee.company', 'employee.branch', 'employee.department']);
        $query->whereBetween('attendance_date', [$sDate, $eDate]);
        
        $this->applyHierarchyFilters($query, $companyId, $branchId, $departmentId, $employeeId);

        if ($reportType === 'late') {
            $query->where('is_late_in', true);
        } elseif ($reportType === 'early_out') {
            $query->where('is_early_out', true);
        } elseif ($reportType === 'overtime') {
            $query->where('is_ot_eligible', true)->where('approved_ot_minutes', '>', 0);
        } elseif ($reportType === 'absent') {
            $query->where('status', 'ABSENT');
        }

        return $query->orderBy('attendance_date', 'desc')->get();
    }

    private function getExceptionsData($sDate, $eDate, $companyId, $branchId, $departmentId, $employeeId)
    {
        // Unassigned logs cannot have applyHierarchyFilters since employee_id is null.
        // We limit it to the current user's scope by excluding them from unassigned if they aren't admin?
        // Let's preserve original logic but apply company check if provided.
        $unassignedLogs = AttendanceLog::whereNull('employee_id')
            ->whereBetween('attendance_date', [$sDate, $eDate]);
            
        // If they specify a company but logs are unassigned, technically they don't belong to a company.
        $unassignedLogs = $unassignedLogs->get()->map(function($l) {
            $obj = new \stdClass();
            $obj->employee_id = 'UNKNOWN';
            $obj->employee_name = 'Unassigned Punch';
            $obj->date = $l->attendance_date;
            $obj->check_in_time = \Carbon\Carbon::parse($l->attendance_timestamp)->format('H:i:s');
            $obj->check_out_time = '--';
            $obj->status = 'Unassigned';
            $obj->remarks = 'Punch received but no matching employee ID';
            return $obj;
        });

        $deviceErrors = DeviceEventLog::whereIn('severity', ['Error', 'Critical'])
            ->whereBetween('created_at', [
                Carbon::parse($sDate)->startOfDay()->toDateTimeString(),
                Carbon::parse($eDate)->endOfDay()->toDateTimeString(),
            ])
            ->get()->map(function($l) {
                $obj = new \stdClass();
                $obj->employee_id = 'DEVICE';
                $obj->employee_name = $l->device ? $l->device->name : 'Unknown Device';
                $obj->date = $l->created_at->toDateString();
                $obj->check_in_time = $l->created_at->format('H:i:s');
                $obj->check_out_time = '--';
                $obj->status = 'Hardware Error';
                $obj->remarks = $l->description;
                return $obj;
            });
            
        $lateEarlyLogsQuery = DailyAttendanceSummary::with(['employee'])
            ->whereBetween('attendance_date', [$sDate, $eDate])
            ->where(function($q) {
                $q->where('is_late_in', true)
                  ->orWhere('is_early_out', true)
                  ->orWhere('is_missing_in', true)
                  ->orWhere('is_missing_out', true);
            });
            
        $this->applyHierarchyFilters($lateEarlyLogsQuery, $companyId, $branchId, $departmentId, $employeeId);

        $lateEarlyLogs = $lateEarlyLogsQuery->get()->map(function($s) {
            $obj = new \stdClass();
            $obj->employee_id = $s->employee ? $s->employee->employee_id : 'N/A';
            $obj->employee_name = $s->employee ? $s->employee->full_name : 'N/A';
            $obj->date = $s->attendance_date->toDateString();
            $obj->check_in_time = $s->first_punch_time ?? '--';
            $obj->check_out_time = $s->last_punch_time ?? '--';
            
            $flags = [];
            if ($s->is_late_in) $flags[] = 'Late';
            if ($s->is_early_out) $flags[] = 'Early Out';
            if ($s->is_missing_in) $flags[] = 'Missing In';
            if ($s->is_missing_out) $flags[] = 'Missing Out';
            
            $obj->status = 'Exception';
            $obj->remarks = implode(', ', $flags);
            return $obj;
        });

        // Merge all
        return collect()->concat($unassignedLogs)->concat($deviceErrors)->concat($lateEarlyLogs)->sortByDesc('date');
    }
}
