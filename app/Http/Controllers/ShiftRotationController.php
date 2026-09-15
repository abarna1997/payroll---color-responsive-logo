<?php

namespace App\Http\Controllers;

use App\Models\ShiftRotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftRotationController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        $rotations = ShiftRotation::orderBy('created_at', 'desc')->get();
        return view('shifts.rotations', compact('rotations'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('attendance.view')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'pattern_type' => 'required|string',
            'start_date' => 'required|date',
        ]);

        ShiftRotation::create([
            'name' => $request->name,
            'pattern_type' => $request->pattern_type,
            'shift_pattern' => $request->shift_pattern ?? [],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'Active',
        ]);

        return back()->with('success', 'Shift rotation created!');
    }

    public function updateStatus(Request $request, $id)
    {
        $rotation = ShiftRotation::findOrFail($id);
        $rotation->update(['status' => $request->status]);
        return back()->with('success', 'Status updated!');
    }

    public function destroy($id)
    {
        ShiftRotation::findOrFail($id)->delete();
        return back()->with('success', 'Shift rotation deleted!');
    }
}
