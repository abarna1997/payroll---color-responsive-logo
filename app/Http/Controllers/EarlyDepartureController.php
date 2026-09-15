<?php

namespace App\Http\Controllers;

use App\Models\EarlyDeparture;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarlyDepartureController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        $earlyDepartures = EarlyDeparture::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        
        return view('early_departures.index', compact('earlyDepartures', 'employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'minutes_early' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        EarlyDeparture::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'minutes_early' => $request->minutes_early,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Early departure record created!');
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $earlyDeparture = EarlyDeparture::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Excused,Unexcused',
        ]);

        $earlyDeparture->update(['status' => $request->status]);

        return back()->with('success', 'Early departure status updated!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('attendance.edit')) {
            return back()->with('error', 'Unauthorized action.');
        }

        EarlyDeparture::findOrFail($id)->delete();
        return back()->with('success', 'Early departure record deleted!');
    }
}
