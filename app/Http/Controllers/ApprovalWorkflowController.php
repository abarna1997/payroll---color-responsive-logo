<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Models\Role;
use App\Models\AccessLevel;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalWorkflowController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('admin.workflows')) {
            abort(403, 'Unauthorized access.');
        }

        $workflows = ApprovalWorkflow::with('steps.role', 'steps.accessLevel')->get();
        $roles = Role::orderBy('sort_order')->get();
        $levels = AccessLevel::orderBy('level')->get();

        return view('approval_workflows.index', compact('workflows', 'roles', 'levels'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.workflows')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string',
            'module' => 'required|string|unique:approval_workflows,module',
            'description' => 'nullable|string',
        ]);

        $workflow = ApprovalWorkflow::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_APPROVAL_WORKFLOW',
            'module' => 'Workflows',
            'record_id' => $workflow->id,
            'new_value' => json_encode($workflow),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Approval workflow added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('admin.workflows')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $workflow = ApprovalWorkflow::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $old = json_encode($workflow);
        $workflow->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_APPROVAL_WORKFLOW',
            'module' => 'Workflows',
            'record_id' => $workflow->id,
            'old_value' => $old,
            'new_value' => json_encode($workflow),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Workflow updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('admin.workflows')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $workflow = ApprovalWorkflow::findOrFail($id);
        $old = json_encode($workflow);

        $workflow->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_APPROVAL_WORKFLOW',
            'module' => 'Workflows',
            'record_id' => $id,
            'old_value' => $old,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Workflow deleted successfully!');
    }

    public function addStep(Request $request, $workflowId)
    {
        if (!Auth::user()->hasPermissionTo('admin.workflows')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'step_name' => 'required|string',
            'step_number' => 'required|integer',
            'approver_type' => 'required|in:Role,User,Department,Branch',
            'role_id' => 'required_if:approver_type,Role|exists:roles,id',
            'access_level_id' => 'nullable|exists:access_levels,id',
            'auto_approve_hours' => 'nullable|integer',
        ]);

        $step = ApprovalStep::create(array_merge(
            $request->all(),
            ['workflow_id' => $workflowId]
        ));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_APPROVAL_STEP',
            'module' => 'Workflows',
            'record_id' => $step->id,
            'new_value' => json_encode($step),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Workflow step added successfully!');
    }

    public function deleteStep(Request $request, $stepId)
    {
        if (!Auth::user()->hasPermissionTo('admin.workflows')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $step = ApprovalStep::findOrFail($stepId);
        $old = json_encode($step);

        $step->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_APPROVAL_STEP',
            'module' => 'Workflows',
            'record_id' => $stepId,
            'old_value' => $old,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Workflow step deleted successfully!');
    }
}
