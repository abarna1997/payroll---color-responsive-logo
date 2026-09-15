<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Company;
use App\Models\DailyAttendanceSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class QaReportAttendanceTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock permission if necessary, or create user with permission
        $this->user = User::factory()->create();
        
        // Ensure user has permission
        $this->actingAs($this->user);
        
        // Create user with a role/permission if necessary or skip it if Gate intercepts
        // If testing report controller, let's just use Gate::before
        Gate::before(function () {
            return true;
        });
    }

    public function test_formatting_with_valid_punches()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);
        
        $summary = DailyAttendanceSummary::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-09-04',
            'check_in' => '2026-09-04 08:30:00',
            'check_out' => '2026-09-04 17:30:00',
            'working_minutes' => 540,
            'overtime_minutes' => 60,
            'late_minutes' => 30,
            'early_out_minutes' => 0,
            'status' => 'Present',
        ]);

        $controller = app(\App\Http\Controllers\ReportController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatReportData');
        $method->setAccessible(true);

        $result = $method->invokeArgs($controller, ['daily', collect([$summary])]);
        
        $formatted = $result->first();
        
        $this->assertEquals('08:30:00', $formatted->check_in_time);
        $this->assertEquals('17:30:00', $formatted->check_out_time);
        $this->assertEquals('09:00', $formatted->working_hours);
        $this->assertEquals('01:00', $formatted->ot_hours);
        $this->assertEquals(30, $formatted->late_minutes);
        $this->assertEquals('Present', $formatted->final_status);
    }

    public function test_formatting_with_null_punches()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);
        
        $summary = DailyAttendanceSummary::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-09-04',
            'check_in' => null,
            'check_out' => null,
            'working_minutes' => 0,
            'overtime_minutes' => 0,
            'late_minutes' => 0,
            'early_out_minutes' => 0,
            'status' => 'Present',
        ]);

        $controller = app(\App\Http\Controllers\ReportController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatReportData');
        $method->setAccessible(true);

        $result = $method->invokeArgs($controller, ['daily', collect([$summary])]);
        
        $formatted = $result->first();
        
        $this->assertEquals('--', $formatted->check_in_time);
        $this->assertEquals('--', $formatted->check_out_time);
        $this->assertEquals('00:00', $formatted->working_hours);
        $this->assertEquals('00:00', $formatted->ot_hours);
        $this->assertEquals('Present', $formatted->final_status);
    }
}
