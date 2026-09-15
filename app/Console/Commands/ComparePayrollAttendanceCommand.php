<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ComparePayrollAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:compare-attendance {period}';

    protected $description = 'Compare OLD Employee::getAttendanceStats() vs NEW PayrollAttendanceService';

    public function handle()
    {
        $periodId = $this->argument('period');
        $period = \App\Models\PayrollPeriod::find($periodId);
        if (!$period) {
            $this->error("Period not found");
            return;
        }

        $employees = \App\Models\Employee::where('status', 'Active')->get();
        $payrollAttendanceService = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $payrollAttendanceService->preloadForPeriod($period);

        $matches = 0;
        $differences = 0;

        $headers = ['Emp ID', 'OLD Late', 'NEW Late', 'OLD OT', 'NEW OT', 'OLD Abs', 'NEW Abs', 'OLD Half', 'NEW Half', 'Result'];
        $rows = [];

        foreach ($employees as $emp) {
            $old = $emp->getAttendanceStats($period->start_date, $period->end_date);
            try {
                $new = $payrollAttendanceService->getAttendanceForPeriod($emp, $period);
            } catch (\Exception $e) {
                $new = null;
            }

            if (!$new) {
                $rows[] = [$emp->employee_id, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'ERROR (Missing)'];
                $differences++;
                continue;
            }

            // Convert OT hours to minutes for old
            $oldOtMin = round($old['ot_hours'] * 60);
            $newOtMin = $new['overtime_minutes'];
            
            $oldLate = $old['late_arrivals']; // This is count, not minutes!
            $newLate = $new['late_minutes']; // We can't strictly compare count vs minutes, just show them

            $oldAbs = $old['absent_days'];
            $newAbs = $new['absent_days'];

            $isMatch = ($oldOtMin == $newOtMin) && ($oldAbs == $newAbs);

            if ($isMatch) {
                $matches++;
                $resultStr = 'MATCH';
            } else {
                $differences++;
                $resultStr = 'DIFF';
            }

            $rows[] = [
                $emp->employee_id,
                $oldLate . ' (count)',
                $newLate . ' (min)',
                $oldOtMin,
                $newOtMin,
                $oldAbs,
                $newAbs,
                '0',
                $new['half_days'],
                $resultStr
            ];
        }

        $this->table($headers, $rows);
        $this->info("Tested: " . $employees->count());
        $this->info("Matches: {$matches}");
        $this->info("Differences: {$differences}");
        $diffPercent = $employees->count() > 0 ? round(($differences / $employees->count()) * 100, 2) : 0;
        $this->info("Difference %: {$diffPercent}%");
    }
}
