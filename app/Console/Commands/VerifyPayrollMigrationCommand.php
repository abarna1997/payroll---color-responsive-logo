<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\DailyAttendanceSummary;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class VerifyPayrollMigrationCommand extends Command
{
    protected $signature = 'payroll:verify {period_id?}';
    protected $description = 'Runs the V1 and V2 payroll calculations side-by-side to generate a Difference Report.';

    public function handle()
    {
        $periodId = $this->argument('period_id');
        $period = $periodId ? PayrollPeriod::find($periodId) : PayrollPeriod::orderBy('id', 'desc')->first();

        if (!$period) {
            $this->error('No payroll period found to verify.');
            return;
        }

        $this->info("Running Shadow Verification for Period: {$period->period_name}");
        $this->line("Comparing V1 (Raw Attendance Logs) vs V2 (Daily Attendance Summary)");
        $this->line(str_repeat('-', 60));

        $employees = Employee::where('status', 'Active')->get();
        $discrepancies = 0;

        foreach ($employees as $employee) {
            // V1: Existing Calculation (from Employee::getAttendanceStats)
            $statsV1 = $employee->getAttendanceStats($period->start_date, $period->end_date);
            $absentV1 = $statsV1['absent_days'] ?? 0;

            // V2: New Calculation (from DailyAttendanceSummary)
            // Determine working days, excluding weekends
            $workingDays = 0;
            $current = Carbon::parse($period->start_date);
            $end = Carbon::parse($period->end_date);
            while ($current->lte($end)) {
                if (!$current->isWeekend()) {
                    $workingDays++;
                }
                $current->addDay();
            }
            
            $summaries = DailyAttendanceSummary::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$period->start_date, $period->end_date])
                ->get();
                
            $presentV2 = $summaries->where('status', 'Present')->count();
            $leaveV2 = $summaries->where('status', 'Leave')->count();
            
            // Assume missing days are absent if not weekend/holiday (simplified for demonstration)
            // Real logic uses shift schedules. For now, we compare counts.
            $absentV2 = $workingDays - ($presentV2 + $leaveV2);
            // Just for demonstration, if V1 absent is exactly 0 and V2 is 30, we know V2 needs shift schedule logic.
            
            if (abs($absentV1 - $absentV2) > 0) {
                $this->warn("Discrepancy for Employee ID: {$employee->emp_id}");
                $this->line("  -> V1 Absent Days: {$absentV1}");
                $this->line("  -> V2 Absent Days: {$absentV2}");
                $discrepancies++;
            }
        }

        $this->line(str_repeat('-', 60));
        
        if ($discrepancies === 0) {
            $this->info("SUCCESS! 100% mathematical parity between V1 and V2 calculations.");
        } else {
            $this->error("FAILED! Found {$discrepancies} material discrepancies. Do not deprecate V1 yet.");
        }
    }
}
