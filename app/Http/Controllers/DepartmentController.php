<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('company')->get();
        $companies = Company::where('status', 'Active')->get();

        return view('departments.index', compact('departments', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'department_code' => 'required|string|unique:departments,department_code',
            'department_name' => 'required|string',
        ]);

        $dept = Department::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_DEPARTMENT',
            'module' => 'Department Management',
            'record_id' => $dept->id,
            'new_value' => json_encode($dept),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Department created successfully!');
    }

    public function update(Request $request, $id)
    {
        $dept = Department::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'department_code' => 'required|string|unique:departments,department_code,' . $id,
            'department_name' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $oldVal = json_encode($dept);
        $dept->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_DEPARTMENT',
            'module' => 'Department Management',
            'record_id' => $dept->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($dept),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Department updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $dept = Department::findOrFail($id);
        $oldVal = json_encode($dept);
        $dept->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_DEPARTMENT',
            'module' => 'Department Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Department deleted successfully!');
    }
}
