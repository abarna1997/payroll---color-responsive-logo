<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Services\Payroll\PayrollAttendanceService;
use App\Services\Payroll\PayrollOTService;
use App\Models\Payslip;

class CompareOTCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:compare-ot {period_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare Current Payslip OT vs New OT Engine';

    /**
     * Execute the console command.
     */
    public function handle(PayrollAttendanceService $attendanceService, PayrollOTService $otService)
    {
        $period = PayrollPeriod::find($this->argument('period_id'));
        if (!$period) {
            $this->error('Payroll period not found.');
            return 1;
        }

        $employees = Employee::where('status', 'Active')->get();
        $this->info("Comparing OT for Period ID: {$period->id} ({$period->start_date} to {$period->end_date})");
        
        $attendanceService->preloadForPeriod($period);
        $tableData = [];

        foreach ($employees as $employee) {
            $payslip = Payslip::where('payroll_period_id', $period->id)->where('employee_id', $employee->id)->first();
            
            try {
                $stats = $attendanceService->getAttendanceForPeriod($employee, $period);
            } catch (\Exception $e) {
                continue; // Skip if no attendance
            }

            $profile = $employee->salaryProfile;
            $basic = $profile ? (float)$profile->basic_salary : 0.00;

            $newResult = $otService->calculateOT($stats, $employee, $period, $basic);
            
            $currentOtAmount = $payslip ? (float)$payslip->ot_payment : 0.00;
            $newOtAmount = $newResult['ot_amount'];
            $diff = round($newOtAmount - $currentOtAmount, 2);

            $tableData[] = [
                $employee->id,
                $employee->name,
                $stats['overtime_minutes'] ?? 0,
                $newResult['ot_payment_enabled'] ? 'Yes' : 'No',
                $newResult['ot_rule_configured'] ? 'Configured' : 'Missing',
                $currentOtAmount,
                $newOtAmount,
                $diff
            ];
        }

        $this->table(
            ['Emp ID', 'Name', 'OT Mins', 'Enabled', 'Formula', 'Current OT', 'New OT', 'Diff'],
            $tableData
        );

        return 0;
    }
}
