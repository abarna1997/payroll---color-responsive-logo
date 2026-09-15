<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('company')->get();
        $companies = Company::where('status', 'Active')->get();

        return view('branches.index', compact('branches', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_code' => 'required|string|unique:branches,branch_code',
            'branch_name' => 'required|string',
            'address' => 'nullable|string',
        ]);

        $branch = Branch::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_BRANCH',
            'module' => 'Branch Management',
            'record_id' => $branch->id,
            'new_value' => json_encode($branch),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Branch created successfully!');
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_code' => 'required|string|unique:branches,branch_code,' . $id,
            'branch_name' => 'required|string',
            'address' => 'nullable|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $oldVal = json_encode($branch);
        $branch->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_BRANCH',
            'module' => 'Branch Management',
            'record_id' => $branch->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($branch),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Branch updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $oldVal = json_encode($branch);
        $branch->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_BRANCH',
            'module' => 'Branch Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Branch deleted successfully!');
    }
}
