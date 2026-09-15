<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Company;
use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::with('company')->withCount('employees')->get();
        $companies = Company::where('status', 'Active')->get();

        // Also merge any ad-hoc employee designations that are not yet registered in the designations table
        $existingTitles = $designations->pluck('title')->toArray();
        $adhocTitles = Employee::whereNotNull('designation')
            ->where('designation', '!=', '')
            ->whereNotIn('designation', $existingTitles)
            ->distinct()
            ->pluck('designation');

        foreach ($adhocTitles as $title) {
            Designation::create([
                'title' => $title,
                'designation_code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 4)),
                'status' => 'Active',
            ]);
        }

        if (count($adhocTitles) > 0) {
            $designations = Designation::with('company')->withCount('employees')->get();
        }

        return view('designations.index', compact('designations', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'designation_code' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
            'description' => 'nullable|string',
        ]);

        $desig = Designation::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_DESIGNATION',
            'module' => 'Designation Management',
            'record_id' => $desig->id,
            'new_value' => json_encode($desig),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Designation created successfully!');
    }

    public function update(Request $request, $id)
    {
        $desig = Designation::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'designation_code' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
            'description' => 'nullable|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $oldVal = json_encode($desig);
        $oldTitle = $desig->title;
        $desig->update($request->all());

        // Update employee records if designation title changed
        if ($oldTitle !== $desig->title) {
            Employee::where('designation', $oldTitle)->update(['designation' => $desig->title]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_DESIGNATION',
            'module' => 'Designation Management',
            'record_id' => $desig->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($desig),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Designation updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $desig = Designation::findOrFail($id);
        $oldVal = json_encode($desig);
        $desig->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_DESIGNATION',
            'module' => 'Designation Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Designation deleted successfully!');
    }
}
