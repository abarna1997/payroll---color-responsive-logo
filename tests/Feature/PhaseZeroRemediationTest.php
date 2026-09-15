<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Payslip;
use App\Models\PayrollPeriod;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PhaseZeroRemediationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role_method_resolves_correctly()
    {
        $user = new User(['role' => 'HR Administrator']);
        
        $this->assertTrue($user->hasRole('HR Administrator'));
        $this->assertTrue($user->hasRole(['Employee', 'HR Administrator']));
        $this->assertFalse($user->hasRole('Employee'));
    }

    public function test_company_scope_isolates_employee_data()
    {
        $company1 = Company::create(['company_name' => 'C1', 'company_code' => 'C01']);
        $company2 = Company::create(['company_name' => 'C2', 'company_code' => 'C02']);
        $branch = Branch::create(['branch_name' => 'B1', 'branch_code' => 'B01', 'company_id' => $company1->id]);
        $dept = Department::create(['department_name' => 'D1', 'department_code' => 'D01', 'company_id' => $company1->id, 'branch_id' => $branch->id]);
        $shift = \App\Models\Shift::create(['shift_name' => 'S1', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'company_id' => $company1->id]);

        $user = User::create([
            'username' => 'mgr', 'email' => 'mgr@ex.com', 'password' => 'pass', 'role' => 'Manager'
        ]);
        
        $employee1 = Employee::create(['first_name' => 'A', 'last_name' => 'B', 'company_id' => $company1->id, 'branch_id' => $branch->id, 'department_id' => $dept->id, 'shift_id' => $shift->id, 'user_id' => $user->id, 'employee_number' => 'E01', 'employee_id' => 'EMP01']);
        $employee2 = Employee::create(['first_name' => 'C', 'last_name' => 'D', 'company_id' => $company2->id, 'branch_id' => $branch->id, 'department_id' => $dept->id, 'shift_id' => $shift->id, 'employee_number' => 'E02', 'employee_id' => 'EMP02']);

        $this->actingAs($user);

        $employees = Employee::all();
        $this->assertTrue($employees->contains('id', $employee1->id));
        $this->assertFalse($employees->contains('id', $employee2->id));
    }

    public function test_payslip_idor_is_prevented()
    {
        $company = Company::create(['company_name' => 'C1', 'company_code' => 'C01']);
        $branch = Branch::create(['branch_name' => 'B1', 'branch_code' => 'B01', 'company_id' => $company->id]);
        $dept = Department::create(['department_name' => 'D1', 'department_code' => 'D01', 'company_id' => $company->id, 'branch_id' => $branch->id]);
        $shift = \App\Models\Shift::create(['shift_name' => 'S1', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'company_id' => $company->id]);
        
        $userA = User::create(['username' => 'a', 'email' => 'a@a.com', 'password' => 'p', 'role' => 'Employee']);
        $employeeA = Employee::create(['first_name' => 'A', 'last_name' => 'B', 'company_id' => $company->id, 'branch_id' => $branch->id, 'department_id' => $dept->id, 'shift_id' => $shift->id, 'user_id' => $userA->id, 'employee_number' => 'E01', 'employee_id' => 'EMP01']);

        $userB = User::create(['username' => 'b', 'email' => 'b@b.com', 'password' => 'p', 'role' => 'Employee']);
        $employeeB = Employee::create(['first_name' => 'C', 'last_name' => 'D', 'company_id' => $company->id, 'branch_id' => $branch->id, 'department_id' => $dept->id, 'shift_id' => $shift->id, 'user_id' => $userB->id, 'employee_number' => 'E02', 'employee_id' => 'EMP02']);

        $period = PayrollPeriod::create(['period_name' => 'Jan', 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'Pending']);

        $payslipB = Payslip::create([
            'employee_id' => $employeeB->id,
            'payroll_period_id' => $period->id,
            'net_salary' => 1000
        ]);

        $this->actingAs($userA);

        $response = $this->get('/payroll/payslip/' . $payslipB->uuid);
        $response->assertStatus(403);
    }

    public function test_strict_attendance_duplicate_protection()
    {
        $company = Company::create(['company_name' => 'C1', 'company_code' => 'C01']);
        $branch = Branch::create(['branch_name' => 'B1', 'branch_code' => 'B01', 'company_id' => $company->id]);
        $dept = Department::create(['department_name' => 'D1', 'department_code' => 'D01', 'company_id' => $company->id, 'branch_id' => $branch->id]);
        $shift = \App\Models\Shift::create(['shift_name' => 'S1', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'company_id' => $company->id]);
        $employee = Employee::create(['first_name' => 'E', 'last_name' => 'E', 'employee_number' => 'E03', 'company_id' => $company->id, 'branch_id' => $branch->id, 'department_id' => $dept->id, 'shift_id' => $shift->id, 'employee_id' => 'EMP03']);
        $timestamp = Carbon::now();

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_date' => $timestamp->toDateString(),
            'attendance_time' => $timestamp->toTimeString(),
            'attendance_timestamp' => $timestamp->toDateTimeString(),
            'source' => 'WEB'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_date' => $timestamp->toDateString(),
            'attendance_time' => $timestamp->toTimeString(),
            'attendance_timestamp' => $timestamp->toDateTimeString(),
            'source' => 'WEB'
        ]);
    }
}
