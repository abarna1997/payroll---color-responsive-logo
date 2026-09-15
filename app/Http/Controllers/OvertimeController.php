<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        $overtimes = Overtime::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        
        return view('overtimes.index', compact('overtimes', 'employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'overtime_date' => 'required|date',
            'hours' => 'required|numeric|min:0.1|max:24',
            'reason' => 'nullable|string',
        ]);

        Overtime::create([
            'employee_id' => $request->employee_id,
            'overtime_date' => $request->overtime_date,
            'hours' => $request->hours,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Overtime record created!');
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $overtime = Overtime::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $overtime->update(['status' => $request->status]);

        return back()->with('success', 'Overtime status updated!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        Overtime::findOrFail($id)->delete();
        return back()->with('success', 'Overtime record deleted!');
    }
}
