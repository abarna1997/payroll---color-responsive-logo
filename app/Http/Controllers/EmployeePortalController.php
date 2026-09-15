<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payslip;
use App\Models\WfhRequest;
use App\Models\AttendanceLog;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use App\Services\WebPunchEligibilityService;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeePortalController extends Controller
{
    /**
     * Show the Employee Portal Dashboard.
     */
    public function dashboard()
    {
        $employee = Auth::user()->employee;
        $recentLogs = AttendanceLog::where('employee_id', $employee->id)
            ->orderBy('attendance_timestamp', 'desc')
            ->take(5)
            ->get();
            
        $wfhRequests = WfhRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();
            
        $upcomingHolidays = Holiday::where('company_id', $employee->company_id)
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date', 'asc')
            ->take(3)
            ->get();

        return view('portal.dashboard', compact('employee', 'recentLogs', 'wfhRequests', 'leaveBalances', 'upcomingHolidays'));
    }

    /**
     * Attendance History
     */
    public function attendance(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereMonth('attendance_timestamp', $month)
            ->whereYear('attendance_timestamp', $year)
            ->orderBy('attendance_timestamp', 'desc')
            ->get();
            
        return view('portal.attendance.index', compact('logs', 'month', 'year'));
    }

    /**
     * WFH Request Listing
     */
    public function wfhIndex()
    {
        $employee = Auth::user()->employee;
        $requests = WfhRequest::where('employee_id', $employee->id)->orderBy('created_at', 'desc')->get();
        return view('portal.wfh.index', compact('requests'));
    }

    /**
     * Store WFH Request
     */
    public function wfhStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
        ]);

        WfhRequest::create([
            'employee_id' => Auth::user()->employee->id,
            'date' => $request->date,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return redirect()->route('portal.wfh.index')->with('success', 'WFH Request submitted successfully.');
    }

    /**
     * Cancel WFH Request
     */
    public function wfhCancel($id)
    {
        $employee = Auth::user()->employee;
        
        $wfhRequest = WfhRequest::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();
            
        if ($wfhRequest->status !== 'Pending') {
            return redirect()->route('portal.wfh.index')->with('error', 'Only pending requests can be cancelled.');
        }
        
        $wfhRequest->update(['status' => 'Cancelled']);
        
        return redirect()->route('portal.wfh.index')->with('success', 'WFH Request cancelled successfully.');
    }

    /**
     * Leave Index (History & Form)
     */
    public function leaveIndex()
    {
        $employee = Auth::user()->employee;
        
        $leaveRequests = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();
            
        $leaveTypes = LeaveType::where('status', 'active')->get();
            
        return view('portal.leave.index', compact('leaveRequests', 'leaveBalances', 'leaveTypes'));
    }

    /**
     * Store Leave Request
     */
    public function leaveStore(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);
        
        // Basic check for overlapping requests could be added here
        
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;
        
        $balance = \App\Models\LeaveBalance::where('employee_id', Auth::user()->employee->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if (!$balance || ($balance->allocated - $balance->used) < $days) {
            return back()->withErrors(['leave_type_id' => 'Insufficient leave balance.']);
        }
        
        \App\Models\LeaveRequest::create([
            'employee_id' => Auth::user()->employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'PENDING',
        ]);

        return redirect()->route('portal.leave.index')->with('success', 'Leave Request submitted successfully.');
    }

    /**
     * Remote Web Punch Page
     */
    public function punchIndex(WebPunchEligibilityService $eligibilityService)
    {
        $employee = Auth::user()->employee;
        
        $eligibility = $eligibilityService->checkEligibility($employee);
        $hasApprovedWfh = $eligibility['eligible'];
        $punchReason = $eligibility['reason'];
            
        return view('portal.punch', compact('hasApprovedWfh', 'punchReason'));
    }

    /**
     * Store Remote Web Punch
     */
    public function punchStore(Request $request, WebPunchEligibilityService $eligibilityService)
    {
        $employee = Auth::user()->employee;
        
        $eligibility = $eligibilityService->checkEligibility($employee);
            
        if (!$eligibility['eligible']) {
            return response()->json(['success' => false, 'message' => $eligibility['reason']], 403);
        }

        // Validate Coordinates
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'punch_state' => 'required|integer', // 0 = In, 1 = Out
        ]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_timestamp' => now(),
            'attendance_date' => now()->toDateString(),
            'attendance_time' => now()->toTimeString(),
            'attendance_type' => $request->punch_state == 0 ? 'Check-In' : 'Check-Out',
            'source' => 'WEB',
            'verification_method' => 'Web Punch',
            'device_serial' => 'WEB-PORTAL',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true, 'message' => 'Punch recorded successfully.']);
    }

    /**
     * Payslips Listing
     */
    public function payslips()
    {
        $employee = Auth::user()->employee;
        $payslips = Payslip::with('payrollPeriod')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('portal.payslips.index', compact('payslips'));
    }

    /**
     * Show single payslip
     */
    public function showPayslip($uuid)
    {
        $payslip = Payslip::with('period')->where('uuid', $uuid)->firstOrFail();
        
        \Illuminate\Support\Facades\Gate::authorize('view', $payslip);
        
        return view('portal.payslips.show', compact('payslip'));
    }

    /**
     * Download Payslip PDF
     */
    public function downloadPayslipPdf($uuid)
    {
        $payslip = Payslip::with('period')->where('uuid', $uuid)->firstOrFail();
        
        \Illuminate\Support\Facades\Gate::authorize('view', $payslip);
        
        $pdf = Pdf::loadView('portal.payslips.pdf', compact('payslip'));
        
        return $pdf->download('payslip-' . $payslip->period->period_name . '.pdf');
    }

    /**
     * View Employee Profile
     */
    public function profile()
    {
        $employee = Auth::user()->employee->load(['department', 'company']);
        return view('portal.profile.index', compact('employee'));
    }
}
