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

class LeaveController extends Controller
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



    /**
     * Manual Logs (Correction requests)
     */




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
    public function leaves()
    {
        $employees = Employee::where('status', 'Active')->orderBy('first_name', 'asc')->get();
        $leaveTypes = LeaveType::all();
        $leaveRequests = LeaveRequest::with('employee', 'leaveType', 'approver')->orderBy('created_at', 'desc')->get();

        return view('leaves.index', compact('employees', 'leaveTypes', 'leaveRequests'));
    }

    public function leaveTypesPage()
    {
        $leaveTypes = LeaveType::orderBy('name', 'asc')->get();
        return view('leaves.types', compact('leaveTypes'));
    }

    public function leaveBalancesPage()
    {
        $employees = Employee::where('status', 'Active')->orderBy('first_name', 'asc')->get();
        $leaveTypes = LeaveType::all();
        $leaveRequests = LeaveRequest::with('employee', 'leaveType', 'approver')->orderBy('created_at', 'desc')->get();
        return view('leaves.balances', compact('employees', 'leaveTypes', 'leaveRequests'));
    }

    public function bulkAssignLeaves(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:Pending,Approved,Rejected',
            'reason' => 'nullable|string',
        ]);

        $employeeIds = $request->employee_ids;
        $leaveTypeId = $request->leave_type_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $status = $request->status;
        $reason = $request->reason;
        $approvedBy = ($status === 'Approved') ? Auth::id() : null;

        foreach ($employeeIds as $empId) {
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $empId,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'reason' => $reason,
                'approved_by' => $approvedBy,
            ]);

            AuditLog::create([
                'user_id' => \Auth::id(),
                'action' => 'BULK_ASSIGN_LEAVE',
                'module' => 'Leave Management',
                'record_id' => $leaveRequest->id,
                'new_value' => json_encode($leaveRequest),
                'ip_address' => $request->ip(),
            ]);
        }

        return back()->with('success', 'Leaves bulk assigned successfully to ' . count($employeeIds) . ' employees.');
    }

    public function storeLeaveType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:leave_types,name',
            'is_paid' => 'required|boolean',
        ]);

        $leaveType = LeaveType::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_LEAVE_TYPE',
            'module' => 'Leave Management',
            'record_id' => $leaveType->id,
            'new_value' => json_encode($leaveType),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave Type category created successfully!');
    }

    public function updateLeaveType(Request $request, $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:leave_types,name,' . $id,
            'status' => 'required|string|in:Active,Inactive',
            'is_paid' => 'required|boolean',
        ]);

        $oldVal = json_encode($leaveType);
        $leaveType->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_LEAVE_TYPE',
            'module' => 'Leave Management',
            'record_id' => $leaveType->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($leaveType),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave Type updated successfully!');
    }

    public function storeLeaveRequest(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $leaveRequest = LeaveRequest::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_LEAVE_REQUEST',
            'module' => 'Leave Management',
            'record_id' => $leaveRequest->id,
            'new_value' => json_encode($leaveRequest),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave request submitted successfully!');
    }

    public function approveLeaveRequest(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $oldVal = json_encode($leaveRequest);

        $leaveRequest->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
        ]);
        
        // Deduct balance
        $balance = \App\Models\LeaveBalance::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', date('Y', strtotime($leaveRequest->start_date)))
            ->first();
            
        if ($balance) {
            $balance->used += $leaveRequest->days;
            $balance->save();
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'APPROVE_LEAVE_REQUEST',
            'module' => 'Leave Management',
            'record_id' => $leaveRequest->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($leaveRequest),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave request has been approved successfully.');
    }

    public function rejectLeaveRequest(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $oldVal = json_encode($leaveRequest);

        $leaveRequest->update([
            'status' => 'Rejected',
            'approved_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'REJECT_LEAVE_REQUEST',
            'module' => 'Leave Management',
            'record_id' => $leaveRequest->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($leaveRequest),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave request has been rejected.');
    }






}
