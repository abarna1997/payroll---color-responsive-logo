<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\DailyAttendanceSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDummyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:generate-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dummy attendance data for 23 employees for July 2026';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching 23 active employees...');
        
        $employees = Employee::where('status', 'Active')->take(23)->get();
        
        if ($employees->isEmpty()) {
            $this->error('No active employees found.');
            return;
        }
        
        $startDate = Carbon::create(2026, 7, 1);
        $endDate = Carbon::create(2026, 7, 31);
        
        $this->info("Generating attendance from {$startDate->toDateString()} to {$endDate->toDateString()} for {$employees->count()} employees.");
        
        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // Clear any existing data for July 2026 for this employee
                DailyAttendanceSummary::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                    ->delete();
                
                $currentDate = $startDate->copy();
                
                // Track no-pay days to ensure we have at least some for testing
                $noPayCount = 0;
                
                while ($currentDate->lte($endDate)) {
                    $isWeekend = $currentDate->isWeekend();
                    
                    // Defaults
                    $status = $isWeekend ? 'OFF_DAY' : 'PRESENT';
                    $workingMinutes = 480; // 8 hours
                    $otMinutes = 0;
                    $lateMinutes = 0;
                    $earlyOutMinutes = 0;
                    
                    // Inject specific scenarios
                    $rand = rand(1, 100);
                    
                    if (!$isWeekend && $rand <= 10 && $noPayCount < 3) {
                        // 10% chance of being absent on a weekday (Max 3 per employee)
                        $status = 'ABSENT';
                        $workingMinutes = 0;
                        $noPayCount++;
                    } elseif (!$isWeekend && $rand > 10 && $rand <= 20) {
                        // 10% chance of late arrival
                        $lateMinutes = rand(15, 60);
                    } elseif (!$isWeekend && $rand > 20 && $rand <= 40) {
                        // 20% chance of normal OT
                        $otMinutes = rand(30, 120);
                    } elseif ($isWeekend && $rand <= 15) {
                        // 15% chance of working on an off day (triggers extra day pay)
                        $workingMinutes = 480;
                        $otMinutes = rand(0, 120); // They might also do OT on the off day
                        $status = 'OFF_DAY'; // Keeping it OFF_DAY but they worked, so engine calculates it
                    } elseif ($currentDate->day == 15) {
                        // Make the 15th a Public Holiday for everyone
                        $status = 'HOLIDAY';
                        $workingMinutes = 0;
                        
                        // 20% chance they worked on the holiday
                        if (rand(1, 100) <= 20) {
                            $workingMinutes = 480;
                            $otMinutes = rand(0, 120);
                        }
                    }

                    if ($status === 'ABSENT' || ($status === 'HOLIDAY' && $workingMinutes == 0) || ($status === 'OFF_DAY' && $workingMinutes == 0)) {
                        $checkIn = null;
                        $checkOut = null;
                    } else {
                        // Dummy punches
                        $checkIn = $currentDate->copy()->setHour(8)->setMinute(0)->addMinutes($lateMinutes);
                        $checkOut = $currentDate->copy()->setHour(17)->setMinute(0)->subMinutes($earlyOutMinutes)->addMinutes($otMinutes);
                    }

                    DailyAttendanceSummary::create([
                        'company_id' => $employee->company_id,
                        'employee_id' => $employee->id,
                        'attendance_date' => $currentDate->copy()->startOfDay(),
                        'shift_id' => $employee->shift_id,
                        'schedule_source' => 'Seeder',
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'late_minutes' => $lateMinutes,
                        'early_out_minutes' => $earlyOutMinutes,
                        'overtime_minutes' => $otMinutes,
                        'working_minutes' => $workingMinutes,
                        'is_wfh' => false,
                        'is_leave' => false,
                        'status' => $status,
                        'is_early_in' => false,
                        'is_late_in' => $lateMinutes > 0,
                        'is_early_out' => $earlyOutMinutes > 0,
                        'is_late_out' => false,
                        'is_ot_eligible' => true,
                        'is_missing_in' => false,
                        'is_missing_out' => false,
                        'is_grace_used' => false,
                        'calculation_version' => 1
                    ]);
                    
                    $currentDate->addDay();
                }
            }
            
            DB::commit();
            $this->info('Successfully generated dummy attendance for July 2026!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to generate attendance: ' . $e->getMessage());
        }
    }
}
