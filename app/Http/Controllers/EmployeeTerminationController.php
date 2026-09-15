<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTermination;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeTerminationController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('employee.view')) {
            abort(403, 'Unauthorized action.');
        }

        $terminations = EmployeeTermination::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        
        return view('terminations.index', compact('terminations', 'employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('employee.delete')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'termination_date' => 'required|date',
            'type' => 'required|in:Voluntary,Involuntary,Disciplinary',
            'reason' => 'nullable|string',
        ]);

        $termination = EmployeeTermination::create([
            'employee_id' => $request->employee_id,
            'termination_date' => $request->termination_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => 'Processed',
        ]);

        $employee = Employee::find($request->employee_id);
        if ($employee) {
            $employee->update(['status' => 'Inactive']);
        }

        return back()->with('success', 'Employee termination processed and access revoked!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('employee.delete')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $termination = EmployeeTermination::findOrFail($id);
        
        $employee = Employee::find($termination->employee_id);
        if ($employee) {
            $employee->update(['status' => 'Active']);
        }
        
        $termination->delete();

        return back()->with('success', 'Termination record deleted and employee access restored!');
    }
}
