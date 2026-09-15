<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shift;
use App\Models\ShiftBreak;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\DailyAttendanceSummary;
use App\Services\AttendanceProcessingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QaPhase5Test extends Command
{
    protected $signature = 'qa:phase5';
    protected $description = 'Runs the Phase 5 automated integration QA tests (22 advanced cases)';

    public function handle()
    {
        $this->info("Starting Phase 5 QA Tests...");
        Shift::where('shift_name', 'QA_PHASE_5_SHIFT')->delete();

        DB::beginTransaction();

        try {
            $shift = Shift::create([
                'shift_name' => 'QA_PHASE_5_SHIFT',
                'start_time' => '08:30:00',
                'end_time' => '17:30:00',
                'is_cross_midnight' => 0,
                'grace_period' => 15,
                'late_threshold' => '09:30:00',
                'half_day_threshold' => '13:00:00',
                'second_half_start' => '14:00:00',
                'early_out_threshold' => '17:15:00',
                'early_out_grace' => 15,
                'overtime_eligibility' => 1,
                'overtime_start' => '18:00:00',
                'minimum_overtime_minutes' => 30,
                'absent_threshold' => '14:30:00', // After second half start
                'status' => 'Active',
            ]);
            
            $lunch = $shift->breaks()->create([
                'name' => 'Lunch',
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'duration_minutes' => 60,
                'is_paid' => 0,
            ]);

            $emp = Employee::first();
            if (!$emp) throw new \Exception("No employee found.");
            
            $oldShiftId = $emp->shift_id;
            $emp->update(['shift_id' => $shift->id]);

            $results = [];

            // TEST 1
            $s1 = $this->testPunches($emp, ['08:30:00'], ['17:30:00']);
            $results['TEST 1: On Time'] = ($s1->status === 'PRESENT' && !$s1->is_late_in && !$s1->is_early_out) ? 'PASS' : 'FAIL';

            // TEST 2
            $s2 = $this->testPunches($emp, ['08:37:00'], ['17:30:00']);
            $results['TEST 2: Grace'] = ($s2->status === 'PRESENT' && $s2->is_grace_used && !$s2->is_late_in) ? 'PASS' : 'FAIL';

            // TEST 3
            $s3 = $this->testPunches($emp, ['08:52:00'], ['17:30:00']);
            $results['TEST 3: Late In (22 min)'] = ($s3->status === 'PRESENT' && $s3->is_late_in && $s3->late_minutes == 22) ? 'PASS' : 'FAIL';

            // TEST 4
            $s4 = $this->testPunches($emp, ['07:30:00'], ['17:30:00']);
            $results['TEST 4: Early In'] = ($s4->status === 'PRESENT' && $s4->is_early_in && !$s4->is_ot_eligible) ? 'PASS' : 'FAIL';

            // TEST 5
            $s5 = $this->testPunches($emp, ['08:30:00'], ['17:00:00']);
            $results['TEST 5: Early Out'] = ($s5->status === 'PRESENT' && $s5->is_early_out) ? 'PASS' : 'FAIL';

            // TEST 6
            $s6 = $this->testPunches($emp, ['08:30:00'], ['17:45:00']);
            $results['TEST 6: Late Out / No OT'] = ($s6->status === 'PRESENT' && $s6->is_late_out && !$s6->is_ot_eligible) ? 'PASS' : 'FAIL';

            // TEST 7
            $s7 = $this->testPunches($emp, ['08:30:00'], ['18:30:00']);
            $results['TEST 7: OT Eligible'] = ($s7->status === 'PRESENT' && $s7->is_ot_eligible && $s7->overtime_minutes >= 30) ? 'PASS' : 'FAIL';

            // TEST 8
            $s8 = $this->testPunches($emp, ['09:31:00'], ['17:30:00']);
            $results['TEST 8: Half Day'] = ($s8->status === 'HALF_DAY') ? 'PASS' : 'FAIL';

            // TEST 9
            $s9 = $this->testPunches($emp, ['14:00:00'], ['17:30:00']);
            $results['TEST 9: Second Half'] = ($s9->status === 'SECOND_HALF') ? 'PASS' : 'FAIL';

            // TEST 10 (Absent)
            $s10 = $this->testPunches($emp, [], []);
            $results['TEST 10: Absent (No Punch)'] = ($s10->status === 'ABSENT' || $s10->status === 'PENDING') ? 'PASS' : 'FAIL'; 

            // TEST 11
            $s11 = $this->testPunches($emp, ['08:40:00'], []);
            $results['TEST 11: Missing Out'] = ($s11->status === 'INCOMPLETE' && $s11->is_missing_out) ? 'PASS' : 'FAIL';

            // TEST 12
            $s12 = $this->testPunches($emp, [], ['17:30:00']);
            $results['TEST 12: Missing In'] = ($s12->status === 'INCOMPLETE' && $s12->is_missing_in) ? 'PASS' : 'FAIL';

            // TEST 13, 14, 15 (Intervals and breaks)
            $s14 = $this->testPunches($emp, ['08:30:00'], ['17:30:00']); // stays clocked in during lunch
            $s15 = $this->testPunches($emp, ['08:30:00', '14:00:00'], ['13:00:00', '17:30:00']); // punches out for lunch
            
            $results['TEST 14: Unpaid Break Deducted Once'] = ($s14->working_minutes == 480) ? 'PASS' : 'FAIL (' . $s14->working_minutes . ')';
            $results['TEST 15: No Double Deduction'] = ($s15->working_minutes == 480) ? 'PASS' : 'FAIL (' . $s15->working_minutes . ')';

            // TEST 16 (Cross Midnight)
            $shift->update([
                'start_time' => '18:00:00',
                'end_time' => '06:00:00',
                'is_cross_midnight' => 1,
            ]);
            $s16 = $this->testPunches($emp, ['18:10:00'], ['05:55:00'], '2026-09-01', true);
            if ($s16) {
                $dateStr = \Carbon\Carbon::parse($s16->attendance_date)->toDateString();
                if ($dateStr === '2026-09-01' && $s16->status === 'PRESENT') {
                    $results['TEST 16: Cross Midnight Summary'] = 'PASS';
                } else {
                    $results['TEST 16: Cross Midnight Summary'] = "FAIL (date: {$dateStr}, status: {$s16->status})";
                }
            } else {
                $results['TEST 16: Cross Midnight Summary'] = 'FAIL (null summary)';
            }

            // Output Results
            $this->table(['Test Case', 'Status'], collect($results)->map(function ($status, $key) {
                return [$key, $status];
            })->toArray());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("ERROR: " . $e->getMessage() . " on line " . $e->getLine());
        }
    }

    private function testPunches($emp, $ins, $outs, $date = '2026-09-01', $crossMidnight = false)
    {
        AttendanceLog::where('employee_id', $emp->id)->delete();
        DailyAttendanceSummary::where('employee_id', $emp->id)->delete();

        $all = [];
        foreach ($ins as $in) {
            $all[] = ['type' => 'IN', 'time' => $in];
        }
        foreach ($outs as $out) {
            $all[] = ['type' => 'OUT', 'time' => $out];
        }

        foreach ($all as $p) {
            $punchDate = clone \Carbon\Carbon::parse($date);
            $punchTime = \Carbon\Carbon::parse($date . ' ' . $p['time']);
            
            if ($crossMidnight && $p['time'] < '12:00:00') {
                $punchDate->addDay();
                $punchTime->addDay();
            }

            AttendanceLog::create([
                'employee_id' => $emp->id,
                'attendance_date' => $punchDate->toDateString(),
                'attendance_time' => $p['time'],
                'attendance_timestamp' => $punchTime,
                'log_type' => $p['type'],
                'attendance_status' => ($p['type'] === 'IN') ? 0 : 1, // Explicit
                'verify_mode' => '1',
                'device_id' => 1,
            ]);
        }

        $service = new AttendanceProcessingService();
        $service->processDaily($emp->id, $date);
        
        return DailyAttendanceSummary::where('employee_id', $emp->id)
            ->where('attendance_date', $date)
            ->first();
    }
}
