<?php

namespace App\Http\Controllers;

use App\Models\WeeklySchedule;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyScheduleController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        $schedules = WeeklySchedule::with('employee')->orderBy('effective_from', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        return view('shifts.weekly', compact('schedules', 'employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'effective_from' => 'required|date',
        ]);

        WeeklySchedule::create($request->all());

        return back()->with('success', 'Weekly schedule created!');
    }

    public function destroy($id)
    {
        WeeklySchedule::findOrFail($id)->delete();
        return back()->with('success', 'Weekly schedule deleted!');
    }
}
