<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTransfer;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeTransferController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('employee.view')) {
            abort(403, 'Unauthorized action.');
        }

        $transfers = EmployeeTransfer::with(['employee', 'previousCompany', 'previousBranch', 'previousDepartment', 'newCompany', 'newBranch', 'newDepartment'])->orderBy('created_at', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $companies = Company::orderBy('company_name')->get();
        $branches = Branch::orderBy('branch_name')->get();
        $departments = Department::orderBy('department_name')->get();
        
        return view('transfers.index', compact('transfers', 'employees', 'companies', 'branches', 'departments'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('employee.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'new_company_id' => 'required|exists:companies,id',
            'new_branch_id' => 'required|exists:branches,id',
            'new_department_id' => 'required|exists:departments,id',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        EmployeeTransfer::create([
            'employee_id' => $employee->id,
            'previous_company_id' => $employee->company_id,
            'previous_branch_id' => $employee->branch_id,
            'previous_department_id' => $employee->department_id,
            'new_company_id' => $request->new_company_id,
            'new_branch_id' => $request->new_branch_id,
            'new_department_id' => $request->new_department_id,
            'transfer_date' => $request->transfer_date,
            'reason' => $request->reason,
            'status' => 'Approved', // Auto approve for now, can implement workflow later
        ]);

        // Update employee record
        $employee->update([
            'company_id' => $request->new_company_id,
            'branch_id' => $request->new_branch_id,
            'department_id' => $request->new_department_id,
        ]);

        return back()->with('success', 'Employee transferred successfully!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('employee.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $transfer = EmployeeTransfer::findOrFail($id);
        
        // Revert transfer if needed
        if ($transfer->status === 'Approved') {
            $employee = Employee::find($transfer->employee_id);
            if ($employee) {
                $employee->update([
                    'company_id' => $transfer->previous_company_id,
                    'branch_id' => $transfer->previous_branch_id,
                    'department_id' => $transfer->previous_department_id,
                ]);
            }
        }
        
        $transfer->delete();

        return back()->with('success', 'Transfer record deleted and reverted successfully!');
    }
}
