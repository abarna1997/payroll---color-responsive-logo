<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\DailyAttendanceSummary;
use App\Models\ShiftBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class QaShiftAttendanceRulesTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;
    protected $shift;
    protected $companyId;
    protected $deviceId;
    protected $date = '2026-09-10';

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyId = DB::table('companies')->insertGetId([
            'company_name' => 'Test Company', 'company_code' => 'TC01', 'created_at' => now(), 'updated_at' => now()
        ]);
        $deptId = DB::table('departments')->insertGetId([
            'department_name' => 'Test Dept', 'department_code' => 'TD01', 'company_id' => $this->companyId, 'created_at' => now(), 'updated_at' => now()
        ]);

        $this->shift = Shift::create([
            'shift_name' => 'Standard Shift',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
            'grace_period' => 15,
            'overtime_eligibility' => true,
            'early_in_threshold' => '07:00:00',
            'late_threshold' => '09:30:00',
            'first_half_end' => '13:30:00',
            'second_half_start' => '13:00:00',
            'absent_threshold' => '14:00:00',
            'early_out_threshold' => '17:00:00',
            'early_out_grace' => 15,
            'overtime_start' => '18:00:00',
            'minimum_overtime_minutes' => 30,
            'is_cross_midnight' => false
        ]);

        $this->deviceId = DB::table('devices')->insertGetId([
            'company_id' => $this->companyId,
            'device_name' => 'Main Gate',
            'device_serial_number' => 'SN123456',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $empId = DB::table('employees')->insertGetId([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'employee_number' => 200,
            'employee_id' => 'E200',
            'company_id' => $this->companyId,
            'department_id' => $deptId,
            'shift_id' => $this->shift->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->employee = Employee::find($empId);
    }

    private function logPunch($time, $type = 'UNKNOWN')
    {
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'attendance_date' => $this->date,
            'attendance_time' => $time,
            'attendance_timestamp' => $this->date . ' ' . $time,
            'attendance_status' => $type === 'IN' ? '0' : ($type === 'OUT' ? '1' : '2'),
            'attendance_type' => $type,
            'source' => 'DEVICE',
            'device_id' => $this->deviceId,
            'company_id' => $this->companyId,
        ]);
    }

    private function process()
    {
        return app(\App\Services\AttendanceProcessingService::class)
            ->processDaily($this->employee->id, $this->date);
    }

    public function test_grace_period_present()
    {
        $this->logPunch('09:10:00', 'IN');
        $this->logPunch('17:30:00', 'OUT');
        $summary = $this->process();
        $this->assertEquals('PRESENT', $summary->status);
        $this->assertTrue($summary->is_grace_used);
        $this->assertFalse($summary->is_late_in);
    }

    public function test_early_in()
    {
        $this->logPunch('08:45:00', 'IN');
        $this->logPunch('17:30:00', 'OUT');
        $summary = $this->process();
        $this->assertEquals('PRESENT', $summary->status);
        $this->assertTrue($summary->is_early_in);
    }

    public function test_late_in()
    {
        $this->logPunch('09:25:00', 'IN');
        $this->logPunch('17:30:00', 'OUT');
        $summary = $this->process();
        $this->assertEquals('PRESENT', $summary->status);
        $this->assertTrue($summary->is_late_in);
        $this->assertEquals(25, $summary->late_minutes);
    }

    public function test_half_day_late()
    {
        $this->logPunch('09:45:00', 'IN');
        $this->logPunch('17:30:00', 'OUT');
        $summary = $this->process();
        $this->assertEquals('HALF_DAY', $summary->status);
    }

    public function test_second_half()
    {
        $this->logPunch('13:10:00', 'IN');
        $this->logPunch('17:30:00', 'OUT');
        $summary = $this->process();
        $this->assertEquals('SECOND_HALF', $summary->status);
    }

    public function test_first_half()
    {
        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('13:00:00', 'OUT');
        $summary = $this->process();
        $this->assertEquals('FIRST_HALF', $summary->status);
        $this->assertTrue($summary->is_early_out);
    }

    // ==========================================
    // REQUIREMENT E: CHECK-OUT SPECIFIC TESTS
    // ==========================================

    // 1. Early Out Tracking OFF
    public function test_early_out_tracking_off()
    {
        $this->shift->update([
            'early_out_threshold' => null,
            'early_out_grace' => 0,
        ]);

        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('16:00:00', 'OUT'); // Early departure with tracking OFF
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertFalse($summary->is_early_out);
        $this->assertEquals(0, $summary->early_out_minutes);
    }

    // 2. Early Out Tracking ON
    public function test_early_out_tracking_on()
    {
        $this->shift->update([
            'early_out_threshold' => '17:00:00',
            'early_out_grace' => 15,
        ]);

        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('17:00:00', 'OUT'); // 17:00 < Grace boundary 17:15
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertTrue($summary->is_early_out);
        $this->assertEquals(30, $summary->early_out_minutes);
    }

    // 3. Normal checkout
    public function test_normal_checkout()
    {
        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('17:30:00', 'OUT'); // At Shift End
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertFalse($summary->is_early_out);
        $this->assertEquals(0, $summary->early_out_minutes);
    }

    // 4. Checkout before Early Out Threshold
    public function test_checkout_before_early_out_threshold()
    {
        $this->shift->update([
            'early_out_threshold' => '17:00:00',
            'early_out_grace' => 15,
        ]);

        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('16:30:00', 'OUT'); // Before 17:00:00
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertTrue($summary->is_early_out);
        $this->assertEquals(60, $summary->early_out_minutes);
    }

    // 5. Checkout inside Early Out Grace
    public function test_checkout_inside_early_out_grace()
    {
        // Shift end = 17:30, grace = 15. Grace boundary = 17:15.
        // Checkout at 17:20 is inside grace (>= 17:15).
        $this->shift->update([
            'early_out_threshold' => '17:00:00',
            'early_out_grace' => 15,
        ]);

        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('17:20:00', 'OUT');
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertFalse($summary->is_early_out);
        $this->assertEquals(0, $summary->early_out_minutes);
    }

    // 6. Checkout outside Early Out Grace
    public function test_checkout_outside_early_out_grace()
    {
        // Shift end = 17:30, grace = 15. Grace boundary = 17:15.
        // Checkout at 17:14 is strictly before grace boundary (< 17:15).
        $this->shift->update([
            'early_out_threshold' => '17:00:00',
            'early_out_grace' => 15,
        ]);

        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('17:14:00', 'OUT');
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertTrue($summary->is_early_out);
        $this->assertEquals(16, $summary->early_out_minutes);
    }

    // 7. Missing checkout
    public function test_missing_checkout()
    {
        $this->logPunch('09:00:00', 'IN');
        $summary = $this->process();

        $this->assertEquals('INCOMPLETE', $summary->status);
        $this->assertTrue($summary->is_missing_out);
        $this->assertFalse($summary->is_early_out);
    }

    // 8. INCOMPLETE precedence
    public function test_incomplete_precedence_over_half_day()
    {
        // Arrive late at 10:00 (would be HALF_DAY), but missing checkout
        $this->logPunch('10:00:00', 'IN');
        $summary = $this->process();

        $this->assertEquals('INCOMPLETE', $summary->status);
        $this->assertTrue($summary->is_missing_out);
        $this->assertFalse($summary->is_early_out);
    }

    // 9. Same-day invalid threshold ordering
    public function test_same_day_invalid_threshold_ordering()
    {
        $user = User::factory()->create([
            'role' => 'Super Administrator',
            'status' => 'Active',
        ]);
        $response = $this->actingAs($user)->post('/shifts', [
            'shift_name' => 'Test Shift Invalid Order',
            'start_time' => '09:00',
            'end_time' => '17:30',
            'grace_period' => 15,
            'is_cross_midnight' => 0,
            'early_out_threshold' => '18:30', // Later than end_time
            'early_out_grace' => 20
        ]);

        $response->assertSessionHasErrors(['early_out_threshold']);
        $errors = session('errors')->get('early_out_threshold');
        $this->assertStringContainsString('Early Out Threshold cannot be later than the Normal End Time for a same-day shift.', $errors[0]);
        $this->assertDatabaseMissing('shifts', ['shift_name' => 'Test Shift Invalid Order']);
    }

    // 10. Cross-midnight shift checkout
    public function test_cross_midnight_shift_checkout()
    {
        $this->shift->update([
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'is_cross_midnight' => true,
            'early_out_threshold' => '05:30:00',
            'early_out_grace' => 15, // Grace boundary = 05:45:00
        ]);

        // Checkout inside grace (05:50 >= 05:45)
        $this->logPunch('22:00:00', 'IN');
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'attendance_date' => \Carbon\Carbon::parse($this->date)->addDay()->format('Y-m-d'),
            'attendance_time' => '05:50:00',
            'attendance_timestamp' => \Carbon\Carbon::parse($this->date)->addDay()->format('Y-m-d') . ' 05:50:00',
            'attendance_status' => '1',
            'attendance_type' => 'OUT',
            'source' => 'DEVICE',
            'device_id' => $this->deviceId,
            'company_id' => $this->companyId,
        ]);
        $summary1 = $this->process();
        $this->assertFalse($summary1->is_early_out);

        // Reset and test checkout outside grace (05:40 < 05:45)
        AttendanceLog::truncate();
        DailyAttendanceSummary::truncate();
        $this->logPunch('22:00:00', 'IN');
        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'attendance_date' => \Carbon\Carbon::parse($this->date)->addDay()->format('Y-m-d'),
            'attendance_time' => '05:40:00',
            'attendance_timestamp' => \Carbon\Carbon::parse($this->date)->addDay()->format('Y-m-d') . ' 05:40:00',
            'attendance_status' => '1',
            'attendance_type' => 'OUT',
            'source' => 'DEVICE',
            'device_id' => $this->deviceId,
            'company_id' => $this->companyId,
        ]);
        $summary2 = $this->process();
        $this->assertTrue($summary2->is_early_out);
        $this->assertEquals(20, $summary2->early_out_minutes);
    }

    // 11. Existing shifts with null/legacy check-out settings
    public function test_existing_shifts_with_null_legacy_checkout_settings()
    {
        $this->shift->update([
            'early_out_threshold' => null,
            'early_out_grace' => 0,
        ]);

        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('17:30:00', 'OUT');
        $summary = $this->process();

        $this->assertEquals('PRESENT', $summary->status);
        $this->assertFalse($summary->is_early_out);
        $this->assertFalse($summary->is_missing_out);
    }

    // 12. Save -> reload -> values remain unchanged
    public function test_save_reload_checkout_values_remain_unchanged()
    {
        $user = User::factory()->create([
            'role' => 'Super Administrator',
            'status' => 'Active',
        ]);
        $response = $this->actingAs($user)->post('/shifts', [
            'shift_name' => 'Persisted Shift',
            'start_time' => '08:30',
            'end_time' => '17:00',
            'grace_period' => 10,
            'is_cross_midnight' => 0,
            'early_out_threshold' => '16:30',
            'early_out_grace' => 20
        ]);

        $response->assertRedirect();
        $shift = Shift::where('shift_name', 'Persisted Shift')->first();
        $this->assertNotNull($shift);
        $this->assertEquals('16:30', \Carbon\Carbon::parse($shift->early_out_threshold)->format('H:i'));
        $this->assertEquals(20, $shift->early_out_grace);
        $this->assertEquals('17:00', \Carbon\Carbon::parse($shift->end_time)->format('H:i'));
    }

    public function test_break_handling()
    {
        ShiftBreak::create([
            'shift_id' => $this->shift->id,
            'name' => 'Lunch',
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'is_paid' => false
        ]);
        
        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('17:00:00', 'OUT');
        $summary = $this->process();
        
        // 8 hours total (480 mins) - 1 hour break (60) = 420 mins
        $this->assertEquals(420, $summary->working_minutes);
    }

    public function test_ot_boundary()
    {
        $this->logPunch('09:00:00', 'IN');
        $this->logPunch('18:45:00', 'OUT'); // 45 mins OT, min is 30.
        $summary = $this->process();
        $this->assertTrue($summary->is_ot_eligible);
        $this->assertEquals(45, $summary->overtime_minutes);
    }
}
