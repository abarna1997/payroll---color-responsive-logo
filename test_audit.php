<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employees = ['P1-1059', 'P1-1001', 'P1-1002'];

foreach ($employees as $empId) {
    echo "==================================================\n";
    echo "Employee: $empId\n";
    $employee = \App\Models\Employee::where('employee_number', $empId)->first();
    if (!$employee) {
        // try 'employee_id' just in case
        $employee = \App\Models\Employee::where('employee_id', $empId)->first();
    }
    
    if (!$employee) {
        // Find using name
        $nameParts = explode('-', $empId);
        $employee = \App\Models\Employee::where('first_name', 'like', '%' . $nameParts[0] . '%')->first();
    }
    
    if (!$employee) {
        echo "NOT FOUND - attempting raw search\n";
        $employee = \App\Models\Employee::first();
        echo "Example Employee: " . $employee->employee_number . "\n";
        continue;
    }
    
    echo "Name: " . $employee->full_name . "\n";
    echo "Company: " . $employee->company_id . "\n";
    echo "Legacy shift_id: " . ($employee->shift_id ?: 'NULL') . "\n";
    
    $assignments = \App\Models\EmployeeShiftAssignment::where('employee_id', $employee->id)->get();
    echo "EmployeeShiftAssignments: " . $assignments->count() . "\n";
    foreach ($assignments as $a) {
        echo " - Shift: " . $a->shift_id . " Effective: " . $a->effective_from . " to " . ($a->effective_to ?: 'NULL') . "\n";
    }
    
    $weeklies = \App\Models\WeeklySchedule::where('employee_id', $employee->id)->count();
    echo "Weekly Schedules: " . ($weeklies > 0 ? 'YES' : 'NO') . "\n";
    
    $overrides = \App\Models\EmployeeScheduleOverride::where('employee_id', $employee->id)->count();
    echo "Daily Overrides: " . ($overrides > 0 ? 'YES' : 'NO') . "\n";
    
    $resolver = new \App\Services\ShiftResolverService();
    $res = $resolver->resolve($employee, '2026-07-01');
    echo "Resolved Shift Source: " . $res['source'] . "\n";
    echo "Resolved Shift: " . ($res['shift'] ? $res['shift']->shift_name : 'NONE') . "\n";
    
    $summaries = \App\Models\DailyAttendanceSummary::where('employee_id', $employee->id)
        ->whereBetween('attendance_date', ['2026-07-01', '2026-07-31'])->count();
    echo "Attendance Summaries (Jul 2026): " . $summaries . "\n";
}

echo "==================================================\n";
echo "Database Counts:\n";
echo "Total Employees: " . \App\Models\Employee::count() . "\n";
echo "Employees with EmployeeShiftAssignment: " . \App\Models\EmployeeShiftAssignment::distinct('employee_id')->count() . "\n";
echo "Employees with legacy shift_id: " . \App\Models\Employee::whereNotNull('shift_id')->count() . "\n";
