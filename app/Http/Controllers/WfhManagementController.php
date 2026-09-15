<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WfhRequest;
use App\Services\Attendance\WfhRequestService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WfhManagementController extends Controller
{
    protected $wfhService;

    public function __construct(WfhRequestService $wfhService)
    {
        $this->wfhService = $wfhService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        // Ensure this works with existing roles, for simplicity loading all if manager/admin, else own
        $employees = collect();
        if (in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager'])) {
            $requests = WfhRequest::with(['employee', 'approver'])
                ->orderBy('date', 'desc')
                ->get();
            $employees = \App\Models\Employee::where('status', 'Active')->orderBy('first_name')->get();
        } else {
            $requests = WfhRequest::with(['shift', 'approver'])
                ->where('employee_id', $employee ? $employee->id : 0)
                ->orderBy('date', 'desc')
                ->get();
        }

        return view('wfh.index', compact('requests', 'employee', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
        ]);

        if ($request->filled('employee_id') && in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager'])) {
            $employee = \App\Models\Employee::find($request->employee_id);
        } else {
            $employee = Auth::user()->employee;
        }

        if (!$employee) {
            return back()->with('error', 'No employee profile found.');
        }

        // Ideally infer shift_id from employee schedule, but using default assigned shift
        $shiftId = $employee->shift_id;
        if (!$shiftId) {
            return back()->with('error', 'You must have an assigned shift to request WFH.');
        }

        try {
            $this->wfhService->submitRequest($employee, $request->date, $shiftId, $request->reason);
            return back()->with('success', 'WFH Request submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $wfhRequest = WfhRequest::findOrFail($id);

        try {
            $this->wfhService->approveRequest($wfhRequest);
            return back()->with('success', 'WFH Request approved.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['Admin', 'Super Admin', 'HR', 'Manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        
        $wfhRequest = WfhRequest::findOrFail($id);

        try {
            $this->wfhService->rejectRequest($wfhRequest, $request->rejection_reason);
            return back()->with('success', 'WFH Request rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $wfhRequest = WfhRequest::findOrFail($id);
        
        if ($wfhRequest->employee_id !== Auth::user()->employee?->id) {
            abort(403);
        }

        try {
            $this->wfhService->cancelRequest($wfhRequest);
            return back()->with('success', 'WFH Request cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
