<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceProcessingService;
use App\Models\Employee;
use Carbon\Carbon;

class ProcessAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process {date?} {--employee=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process raw attendance logs into daily summaries (Shadow Mode)';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceProcessingService $service)
    {
        $dateStr = $this->argument('date') ?: now()->toDateString();
        $employeeId = $this->option('employee');

        $this->info("Starting Shadow Mode Attendance Processing for {$dateStr}");

        $query = Employee::query();
        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        $employees = $query->get();
        $this->withProgressBar($employees, function ($employee) use ($service, $dateStr) {
            $service->processDaily($employee->id, $dateStr);
        });

        $this->newLine();
        $this->info('Attendance processing complete!');
    }
}
