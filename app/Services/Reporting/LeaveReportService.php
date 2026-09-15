<?php

namespace App\Services\Reporting;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use stdClass;

class LeaveReportService
{
    /**
     * Get summary of leave usage per department.
     */
    public function getDepartmentLeaveSummary()
    {
        return LeaveRequest::join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.department_name', DB::raw('count(leave_requests.id) as total_leaves'), DB::raw('sum(leave_requests.number_of_days) as total_days'))
            ->where('leave_requests.status', 'Approved')
            ->groupBy('departments.id', 'departments.department_name')
            ->get();
    }

    /**
     * Get leave history for a specific employee.
     */
    public function getEmployeeLeaveHistory($employeeId)
    {
        return LeaveRequest::with('leaveType')
            ->where('employee_id', $employeeId)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get current leave balances for all employees.
     */
    public function getLeaveBalances($companyId = null, $departmentId = null)
    {
        $query = LeaveBalance::with(['employee.company', 'employee.department', 'leaveType']);

        if ($companyId) {
            $query->whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->get();
    }
}
