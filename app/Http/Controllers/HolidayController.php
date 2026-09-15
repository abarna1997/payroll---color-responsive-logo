<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::with('company')->get();
        $companies = Company::where('status', 'Active')->get();

        return view('holidays.index', compact('holidays', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'holiday_name' => 'required|string',
            'holiday_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_HOLIDAY',
            'module' => 'Holiday Management',
            'record_id' => $holiday->id,
            'new_value' => json_encode($holiday),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday created successfully!');
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'holiday_name' => 'required|string',
            'holiday_date' => 'required|date',
        ]);

        $oldVal = json_encode($holiday);
        $holiday->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_HOLIDAY',
            'module' => 'Holiday Management',
            'record_id' => $holiday->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($holiday),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);
        $oldVal = json_encode($holiday);
        $holiday->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_HOLIDAY',
            'module' => 'Holiday Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday deleted successfully!');
    }
}
