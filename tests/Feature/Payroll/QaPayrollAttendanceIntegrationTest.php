<?php

namespace Tests\Feature\Payroll;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QaPayrollAttendanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_reads_daily_attendance_summary(): void
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test Period',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        \App\Models\DailyAttendanceSummary::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-09-05',
            'status' => 'LATE',
            'late_minutes' => 45,
            'working_minutes' => 480,
            'overtime_minutes' => 0,
            'early_out_minutes' => 0,
        ]);

        \App\Models\DailyAttendanceSummary::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-09-06',
            'status' => 'OVERTIME',
            'late_minutes' => 0,
            'working_minutes' => 540,
            'overtime_minutes' => 60,
            'early_out_minutes' => 0,
        ]);

        \App\Models\DailyAttendanceSummary::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-09-07',
            'status' => 'ABSENT',
            'late_minutes' => 0,
            'working_minutes' => 0,
            'overtime_minutes' => 0,
            'early_out_minutes' => 0,
        ]);

        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $result = $service->getAttendanceForPeriod($employee, $period);

        $this->assertEquals(2, $result['present_days']);
        $this->assertEquals(1, $result['absent_days']);
        $this->assertEquals(45, $result['late_minutes']);
        $this->assertEquals(60, $result['overtime_minutes']);
        $this->assertEquals(1020, $result['working_minutes']);
    }

    public function test_missing_attendance_throws_exception()
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test Period',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Attendance has not been processed for this payroll period.");

        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $service->getAttendanceForPeriod($employee, $period);
    }

    public function test_attendance_status_transfer()
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test Period',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-01', 'status' => 'ABSENT']);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-02', 'status' => 'HALF_DAY']);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-03', 'status' => 'FIRST_HALF']);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-04', 'status' => 'SECOND_HALF']);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-05', 'status' => 'HOLIDAY', 'working_minutes' => 0]);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-06', 'status' => 'OFF_DAY', 'working_minutes' => 0]);

        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $result = $service->getAttendanceForPeriod($employee, $period);

        $this->assertEquals(1, $result['absent_days']);
        $this->assertEquals(3, $result['half_days']);
        $this->assertEquals(0, $result['present_days']);
    }

    public function test_worked_on_non_working_day()
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-05', 'status' => 'HOLIDAY', 'working_minutes' => 480]);
        
        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $result = $service->getAttendanceForPeriod($employee, $period);

        $this->assertEquals(1, $result['present_days']);
    }

    public function test_paid_unpaid_leave_distinction()
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        $paidType = \App\Models\LeaveType::create(['name' => 'Annual', 'status' => 'Active', 'is_paid' => true]);
        $unpaidType = \App\Models\LeaveType::create(['name' => 'No Pay', 'status' => 'Active', 'is_paid' => false]);

        \App\Models\LeaveRequest::create(['employee_id' => $employee->id, 'leave_type_id' => $paidType->id, 'start_date' => '2026-09-10', 'end_date' => '2026-09-10', 'status' => 'Approved']);
        \App\Models\LeaveRequest::create(['employee_id' => $employee->id, 'leave_type_id' => $unpaidType->id, 'start_date' => '2026-09-11', 'end_date' => '2026-09-11', 'status' => 'Approved']);

        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-10', 'status' => 'LEAVE']);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-11', 'status' => 'LEAVE']);

        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        // We shouldn't preload for tests individually unless we call it, but let's test without preload
        $result = $service->getAttendanceForPeriod($employee, $period);

        $this->assertEquals(1, $result['paid_leaves']);
        $this->assertEquals(1, $result['unpaid_leaves']);
        $this->assertEquals(1, $result['v_days']); // Unpaid leaf contributes to NoPay V days
    }

    public function test_cross_midnight_shift_is_consumed_as_is()
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        // Just one summary row representing the logical date
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-01', 'status' => 'PRESENT', 'working_minutes' => 720]);

        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $result = $service->getAttendanceForPeriod($employee, $period);

        $this->assertEquals(1, $result['present_days']);
        $this->assertEquals(720, $result['working_minutes']);
    }

    public function test_payroll_period_boundaries()
    {
        $employee = \App\Models\Employee::factory()->create();
        $period = \App\Models\PayrollPeriod::create([
            'period_name' => 'Test',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
        ]);

        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-08-31', 'status' => 'PRESENT', 'working_minutes' => 480]);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-01', 'status' => 'PRESENT', 'working_minutes' => 480]);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-09-30', 'status' => 'PRESENT', 'working_minutes' => 480]);
        \App\Models\DailyAttendanceSummary::create(['employee_id' => $employee->id, 'attendance_date' => '2026-10-01', 'status' => 'PRESENT', 'working_minutes' => 480]);

        $service = app(\App\Services\Payroll\PayrollAttendanceService::class);
        $result = $service->getAttendanceForPeriod($employee, $period);

        $this->assertEquals(2, $result['present_days']); // Only 01 Sep and 30 Sep
    }

    public function test_architecture_dependencies()
    {
        // Assert PayrollCalculationService does not query attendance_logs
        $content = file_get_contents(app_path('Services/Payroll/PayrollCalculationService.php'));
        $this->assertStringNotContainsString('attendance_logs', $content);
        $this->assertStringNotContainsString('AttendanceLog', $content);
        // getAttendanceStats might be mentioned in comments, which is fine as long as not called
        // We removed the comment so it shouldn't be there
        $this->assertStringNotContainsString('getAttendanceStats', $content);
    }
}
