<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayrollPeriod;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Str;

class PayslipSecurityTest extends TestCase
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

    private function createEmployee(User $user)
    {
        return Employee::create([
            'employee_id' => 'TEST-' . rand(1000, 9999),
            'first_name' => 'Test',
            'last_name' => 'User',
            'status' => 'Active',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'employee_number' => 'TC-' . rand(1000, 9999),
            'user_id' => $user->id,
        ]);
    }

    public function test_employee_can_view_own_payslip()
    {
        $userA = User::factory()->create(['role' => 'Employee']);
        $employeeA = $this->createEmployee($userA);
        
        $periodId = \Illuminate\Support\Facades\DB::table('payroll_periods')->insertGetId(['company_id' => $this->company->id, 'period_name' => 'July 2026', 'start_date' => '2026-07-01', 'end_date' => '2026-07-31', 'status' => 'Locked']);

        $payslipId = \Illuminate\Support\Facades\DB::table('payslips')->insertGetId(['company_id' => $this->company->id, 'employee_id' => $employeeA->id, 'payroll_period_id' => $periodId, 'uuid' => Str::uuid()]);
        $payslip = Payslip::withoutGlobalScopes()->find($payslipId);

        $response = $this->actingAs($userA)->get("/portal/payslips/{$payslip->uuid}");
        
        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_others_payslip()
    {
        $userA = User::factory()->create(['role' => 'Employee']);
        $employeeA = $this->createEmployee($userA);
        
        $userB = User::factory()->create(['role' => 'Employee']);
        $employeeB = $this->createEmployee($userB);

        $period = $periodId = \Illuminate\Support\Facades\DB::table('payroll_periods')->insertGetId(['company_id' => $this->company->id, 'period_name' => 'July 2026', 'start_date' => '2026-07-01', 'end_date' => '2026-07-31', 'status' => 'Locked']);

        $payslipB = $payslipId = \Illuminate\Support\Facades\DB::table('payslips')->insertGetId(['company_id' => $this->company->id, 'employee_id' => $employeeB->id, 'payroll_period_id' => $periodId, 'uuid' => Str::uuid()]);
        $payslipB = Payslip::withoutGlobalScopes()->find($payslipId);

        $response = $this->actingAs($userA)->get("/portal/payslips/{$payslipB->uuid}");
        
        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_payslip()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        
        $userB = User::factory()->create(['role' => 'Employee']);
        $employeeB = $this->createEmployee($userB);

        $period = $periodId = \Illuminate\Support\Facades\DB::table('payroll_periods')->insertGetId(['company_id' => $this->company->id, 'period_name' => 'July 2026', 'start_date' => '2026-07-01', 'end_date' => '2026-07-31', 'status' => 'Locked']);

        $payslipB = $payslipId = \Illuminate\Support\Facades\DB::table('payslips')->insertGetId(['company_id' => $this->company->id, 'employee_id' => $employeeB->id, 'payroll_period_id' => $periodId, 'uuid' => Str::uuid()]);
        $payslipB = Payslip::withoutGlobalScopes()->find($payslipId);

        $response = $this->actingAs($admin)->get("/payroll/payslip/{$payslipB->uuid}");
        
        $response->assertStatus(200);
    }
}
