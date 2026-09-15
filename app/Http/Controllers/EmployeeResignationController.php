<?php

namespace App\Http\Controllers;

use App\Models\EmployeeResignation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeResignationController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('employee.view')) {
            abort(403, 'Unauthorized action.');
        }

        $resignations = EmployeeResignation::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        
        return view('resignations.index', compact('resignations', 'employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('employee.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'resignation_date' => 'required|date',
            'last_working_day' => 'nullable|date|after_or_equal:resignation_date',
            'reason' => 'nullable|string',
        ]);

        EmployeeResignation::create([
            'employee_id' => $request->employee_id,
            'resignation_date' => $request->resignation_date,
            'last_working_day' => $request->last_working_day,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Employee resignation recorded successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('employee.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $resignation = EmployeeResignation::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Accepted,Rejected',
        ]);

        $resignation->update(['status' => $request->status]);
        
        if ($request->status === 'Accepted') {
            $employee = Employee::find($resignation->employee_id);
            if ($employee) {
                // If today is past last working day, make them inactive, else leave Active till corn job does it.
                // For simplicity, we just mark status.
            }
        }

        return back()->with('success', 'Resignation status updated!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('employee.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        EmployeeResignation::findOrFail($id)->delete();
        return back()->with('success', 'Resignation record deleted!');
    }
}
