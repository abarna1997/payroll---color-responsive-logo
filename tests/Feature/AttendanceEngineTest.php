<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\DailyAttendanceSummary;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AttendanceEngineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::create([
            'company_name' => 'Test Company',
            'status' => 'Active',
            'company_code' => 'TC'
        ]);
        
        $this->department = Department::create([
            'department_name' => 'Test Department',
            'department_code' => 'TD',
            'company_id' => $this->company->id,
            ]);
    }

    public function test_engine_creates_daily_summary_from_raw_log()
    {
        $employee = Employee::create([
            'employee_id' => 'TEST-' . rand(1000, 9999),
            'first_name' => 'Test',
            'last_name' => 'User',
            'status' => 'Active',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'employee_number' => 'TC-' . rand(1000, 9999),
        ]);
        
        $time = Carbon::now();

        $log = new AttendanceLog([
            'employee_id' => $employee->id,
            'attendance_timestamp' => $time->format('Y-m-d H:i:s'),
        ]);

        $service = new AttendanceEngineService();
        $summary = $service->processRawLog($log);

        $this->assertInstanceOf(DailyAttendanceSummary::class, $summary);
        $this->assertEquals($employee->id, $summary->employee_id);
        $this->assertEquals($time->format('Y-m-d'), $summary->attendance_date->format('Y-m-d'));
        $this->assertNotNull($summary->check_in);
    }
}
