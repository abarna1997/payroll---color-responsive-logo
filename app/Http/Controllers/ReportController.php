<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Reporting\AttendanceReportService;
use App\Services\Attendance\AttendanceRecalculationService;
use App\Services\Reporting\ReportQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    protected $attendanceReportService;
    protected $recalculationService;
    protected $reportQueryService;

    public function __construct(
        AttendanceReportService $attendanceReportService,
        AttendanceRecalculationService $recalculationService,
        ReportQueryService $reportQueryService
    ) {
        $this->attendanceReportService = $attendanceReportService;
        $this->recalculationService = $recalculationService;
        $this->reportQueryService = $reportQueryService;
    }

    private function authorizeReportType($reportType)
    {
        if (!$reportType) return;

        $permissions = [
            'daily' => 'report.attendance',
            'monthly' => 'report.attendance',
            'late' => 'report.attendance',
            'early_out' => 'report.attendance',
            'absent' => 'report.attendance',
            'history' => 'report.employee',
            'department' => 'report.attendance',
            'branch' => 'report.attendance',
            'company' => 'report.attendance',
            'device' => 'device.view',
            'transaction' => 'report.attendance',
            'exceptions' => 'report.attendance',
            'overtime' => 'report.payroll',
        ];

        $requiredPermission = $permissions[$reportType] ?? 'report.view';

        if (!\Illuminate\Support\Facades\Auth::user()->hasPermissionTo($requiredPermission)) {
            abort(403, 'Unauthorized access to this report type.');
        }
    }

    public function index(Request $request)
    {
        $companies = Company::all();
        $branches = Branch::all();
        $departments = Department::all();
        $employees = Employee::all();

        $reportType = $request->input('report_type');
        $this->authorizeReportType($reportType);

        if ($request->input('action') === 'recalculate' && $reportType) {
            $recalcCount = $this->recalculationService->recalculateLogs($request->all(), $request->ip());
            session()->flash('success', "Successfully recalculated and updated {$recalcCount} attendance logs in the database!");
        }

        $data = [];

        if ($reportType) {
            $rawRecords = $this->reportQueryService->getReportData($reportType, $request->all());
            $data = $this->formatReportData($reportType, $rawRecords);
        }

        return view('reports.index', compact('data', 'companies', 'branches', 'departments', 'employees', 'reportType'));
    }

    public function exportCsv(Request $request)
    {
        $reportType = $request->input('report_type');
        if (!$reportType) {
            return redirect()->back()->with('error', 'Select a report to export');
        }
        
        // Ensure export authorization matches UI
        if (!Gate::allows('report.export')) {
            abort(403, 'You do not have permission to export reports.');
        }
        $this->authorizeReportType($reportType);

        $rawRecords = $this->reportQueryService->getReportData($reportType, $request->all());
        $data = $this->formatReportData($reportType, $rawRecords);

        if (empty($data) || (isset($data['unassigned']) && count($data['unassigned']) == 0 && count($data['late_early']) == 0 && count($data['errors']) == 0)) {
            return redirect()->back()->with('error', 'No data to export.');
        }

        $filename = $reportType . '_report_' . date('Y_m_d_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            if ($reportType === 'exceptions') {
                fputcsv($file, ['Employee ID', 'Name', 'Date', 'Check In', 'Check Out', 'Status', 'Remarks']);
                
                foreach ($data['unassigned'] as $row) {
                    fputcsv($file, [$row->employee_id, $row->employee_name, $row->date, $row->check_in_time, $row->check_out_time, $row->status, $row->remarks]);
                }
                foreach ($data['errors'] as $row) {
                    fputcsv($file, [$row->employee_id, $row->employee_name, $row->date, $row->check_in_time, $row->check_out_time, $row->status, $row->remarks]);
                }
                foreach ($data['late_early'] as $row) {
                    fputcsv($file, [$row->employee_id, $row->employee_name, $row->date, $row->check_in_time, $row->check_out_time, $row->status, $row->remarks]);
                }
            } elseif ($reportType === 'transaction') {
                fputcsv($file, ['Employee ID', 'Name', 'Company', 'Department', 'Timestamp', 'Device', 'Status']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->employee ? $row->employee->employee_id : 'N/A',
                        $row->employee ? $row->employee->full_name : 'N/A',
                        $row->employee && $row->employee->company ? $row->employee->company->company_name : 'N/A',
                        $row->employee && $row->employee->department ? $row->employee->department->department_name : 'N/A',
                        $row->attendance_timestamp,
                        $row->device ? $row->device->name : 'N/A',
                        $row->attendance_status
                    ]);
                }
            } else {
                fputcsv($file, ['Employee ID', 'Name', 'Company', 'Branch', 'Department', 'Date', 'Check In Time', 'Check In Status', 'Check Out Time', 'Check Out Status', 'Working Hours', 'Late (Mins)', 'Early Out (Mins)', 'OT (Hours)', 'Final Status']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->employee_id, $row->employee_name, $row->company, $row->branch, $row->department,
                        $row->date, $row->check_in_time, $row->check_in_status,
                        $row->check_out_time, $row->check_out_status,
                        $row->working_hours, $row->late_minutes, $row->early_out_minutes,
                        $row->ot_hours, $row->final_status
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    private function formatReportData($reportType, $rawRecords)
    {
        if ($reportType === 'exceptions' || $reportType === 'transaction') {
            return $rawRecords;
        }
        
        return $rawRecords->map(function ($s) {
            $obj = new \stdClass();
            $obj->employee_id = $s->employee ? $s->employee->employee_id : 'N/A';
            $obj->employee_name = $s->employee ? $s->employee->full_name : 'N/A';
            $obj->company = $s->employee && $s->employee->company ? $s->employee->company->company_name : 'N/A';
            $obj->branch = $s->employee && $s->employee->branch ? $s->employee->branch->branch_name : 'N/A';
            $obj->department = $s->employee && $s->employee->department ? $s->employee->department->department_name : 'N/A';
            
            $obj->date = $s->attendance_date->toDateString();
            $obj->check_in_time = $s->check_in ? \Carbon\Carbon::parse($s->check_in)->format('H:i:s') : '--';
            
            $inStatus = '--';
            if ($s->is_late_in) $inStatus = 'Late';
            elseif ($s->is_early_in) $inStatus = 'Early';
            elseif ($s->check_in) $inStatus = 'On Time';
            $obj->check_in_status = $inStatus;
            
            $obj->check_out_time = $s->check_out ? \Carbon\Carbon::parse($s->check_out)->format('H:i:s') : '--';
            
            $outStatus = '--';
            if ($s->is_early_out) $outStatus = 'Early Out';
            elseif ($s->is_late_out) $outStatus = 'Late Out';
            elseif ($s->check_out) $outStatus = 'On Time';
            $obj->check_out_status = $outStatus;
            
            $hours = floor(($s->working_minutes ?? 0) / 60);
            $mins = ($s->working_minutes ?? 0) % 60;
            $obj->working_hours = sprintf('%02d:%02d', $hours, $mins);
            
            $obj->late_minutes = $s->late_minutes ?? 0;
            $obj->early_out_minutes = $s->early_out_minutes ?? 0;
            
            $otHours = floor(($s->overtime_minutes ?? 0) / 60);
            $otMins = ($s->overtime_minutes ?? 0) % 60;
            $obj->ot_hours = sprintf('%02d:%02d', $otHours, $otMins);
            
            $obj->final_status = $s->status;
            return $obj;
        });
    }
}
