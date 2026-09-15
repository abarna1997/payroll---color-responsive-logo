<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryComponentController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('manage_settings')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_component_id' => 'required|exists:salary_components,id',
            'value' => 'nullable|numeric',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from'
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $component = SalaryComponent::findOrFail($request->salary_component_id);

        if ($employee->company_id !== $component->company_id) {
            abort(403, 'Cross-company component assignment is prohibited.');
        }

        // Authorize company access
        if (Auth::user()->role !== 'Super Administrator') {
            if ($employee->company_id !== Auth::user()->employee->company_id) {
                abort(403, 'Unauthorized company.');
            }
        }

        $assignment = EmployeeSalaryComponent::create([
            'employee_id' => $employee->id,
            'salary_component_id' => $component->id,
            'value' => $request->value,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'is_active' => true,
        ]);

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'ASSIGN_SALARY_COMPONENT',
            'module' => 'Payroll',
            'record_id' => $assignment->id,
            'new_value' => json_encode($assignment),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Component assigned successfully.');
    }
}
