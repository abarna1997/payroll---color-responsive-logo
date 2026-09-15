<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Services\Payroll\APITService;
use App\Services\Payroll\PayrollTaxService;

class CompareTaxCommand extends Command
{
    protected $signature = 'payroll:compare-tax {period_id}';
    protected $description = 'Compare Old APIT vs New Tax Engine';

    public function handle(APITService $oldService, PayrollTaxService $newService)
    {
        $period = PayrollPeriod::find($this->argument('period_id'));
        if (!$period) {
            $this->error('Payroll period not found.');
            return 1;
        }

        $employees = Employee::where('status', 'Active')->get();
        $this->info("Comparing tax for Period ID: {$period->id} ({$period->start_date} to {$period->end_date})");
        
        $tableData = [];

        foreach ($employees as $employee) {
            $profile = $employee->salaryProfile;
            $basic = $profile ? (float)$profile->basic_salary : 0.00;
            $incentive = $profile ? (float)$profile->incentive : 0.00;
            $gross = $basic + $incentive;

            if ($gross <= 0) continue;

            $oldTax = $oldService->calculateTax($gross);
            $newResult = $newService->calculateTax($gross, $employee, $period);
            $newTax = $newResult['tax_amount'];

            $diff = round($newTax - $oldTax, 2);

            $tableData[] = [
                $employee->id,
                $employee->name,
                $oldTax,
                $newTax,
                $diff,
                $newResult['tax_year_id'] ?? 'N/A',
                $newResult['tax_rule_version'] ?? 'N/A',
                $diff != 0 ? 'Differs' : 'Match'
            ];
        }

        $this->table(
            ['Emp ID', 'Name', 'Old Tax', 'New Tax', 'Diff', 'Tax Year ID', 'Rule Version', 'Status'],
            $tableData
        );

        return 0;
    }
}
