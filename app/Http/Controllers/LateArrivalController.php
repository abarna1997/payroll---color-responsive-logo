<?php

namespace App\Http\Controllers;

use App\Models\LateArrival;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LateArrivalController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        $lateArrivals = LateArrival::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        
        return view('late_arrivals.index', compact('lateArrivals', 'employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'minutes_late' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        LateArrival::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'minutes_late' => $request->minutes_late,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Late arrival record created!');
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $lateArrival = LateArrival::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Excused,Unexcused',
        ]);

        $lateArrival->update(['status' => $request->status]);

        return back()->with('success', 'Late arrival status updated!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        LateArrival::findOrFail($id)->delete();
        return back()->with('success', 'Late arrival record deleted!');
    }
}
