<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_reports()
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'status' => 'Active'
        ]);

        $company = Company::create(['company_name' => 'Test Company', 'company_code' => 'TC', 'status' => 'Active']);
        $branch = Branch::create(['branch_name' => 'Test Branch', 'branch_code' => 'TB', 'company_id' => $company->id]);
        $department = Department::create(['department_name' => 'Test Dept', 'department_code' => 'TD', 'company_id' => $company->id]);
        
        $employee = Employee::create([
            'employee_id' => 'EMP101',
            'employee_number' => 'E101',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'Active',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id
        ]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'attendance_time' => '08:00:00',
            'attendance_timestamp' => now()->startOfDay()->addHours(8),
            'attendance_type' => 'Check-In',
            'attendance_status' => 'Present',
            'device_serial' => 'DEV101',
            'raw_data' => 'RAW'
        ]);

        $response = $this->actingAs($admin)->get(route('reports', [
            'report_type' => 'daily',
            'company_id' => $company->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString()
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('data');
    }
}
