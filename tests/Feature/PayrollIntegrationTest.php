<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $employeeUser;
    protected $employee;
    protected $payrollPeriod;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::create(['company_code' => 'CA', 'company_name' => 'Company A']);
        $dept = Department::create(['department_name' => 'IT', 'department_code' => 'IT', 'company_id' => $this->company->id]);
        $desig = Designation::create(['title' => 'Dev', 'designation_code' => 'DEV', 'company_id' => $this->company->id]);
        
        $this->adminUser = User::factory()->create(['role' => 'Admin', ]);
        $this->employeeUser = User::factory()->create(['role' => 'Employee', 'status' => 'Active']);
        
        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->employeeUser->id,
            'employee_id' => 'E01',
            'employee_number' => 'E01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $dept->id,
            'status' => 'Active'
        ]);
        
        \App\Models\SalaryProfile::create([
            'employee_id' => $this->employee->id,
            'basic_salary' => 50000,
            'allowances_json' => json_encode([]),
            'deductions_json' => json_encode([]),
        ]);

        Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'employee_id' => 'A01',
            'employee_number' => 'A01',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'department_id' => $dept->id,
            'status' => 'Active'
        ]);

        $periodId = \Illuminate\Support\Facades\DB::table('payroll_periods')->insertGetId([
            'company_id' => $this->company->id,
            'period_name' => 'August 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $this->payrollPeriod = PayrollPeriod::withoutGlobalScopes()->find($periodId);
    }

    public function test_payroll_generation_requires_shadow_mode()
    {
        // Try to generate without shadow mode token
        $response = $this->actingAs($this->adminUser)->postJson("/payroll/periods/{$this->payrollPeriod->id}/process", [
            'shadow_token' => 'invalid_token'
        ]);
        
        // Either 403 or Validation Error (422) depending on implementation
        $response->assertStatus(422);
    }

    public function test_employee_can_view_own_payslip_but_not_others()
    {
        $payslipUuid = (string) \Illuminate\Support\Str::uuid();
        $payslipId = \Illuminate\Support\Facades\DB::table('payslips')->insertGetId([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_period_id' => $this->payrollPeriod->id,
            'uuid' => $payslipUuid,
            'basic_salary' => 50000,
            'gross_salary' => 50000,
            'net_salary' => 50000,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $payslip = Payslip::withoutGlobalScopes()->find($payslipId);

        $response = $this->actingAs($this->employeeUser)->get("/portal/payslips/{$payslip->uuid}");
        $response->assertStatus(200);

        $otherEmployeeUser = User::factory()->create(['role' => 'Employee', 'status' => 'Active']);
        Employee::create([
            'company_id' => $this->company->id,
            'user_id' => $otherEmployeeUser->id,
            'employee_id' => 'E02',
            'employee_number' => 'E02',
            'first_name' => 'Other',
            'last_name' => 'Emp',
            'department_id' => $this->employee->department_id,
            'status' => 'Active'
        ]);

        $response2 = $this->actingAs($otherEmployeeUser)->get("/portal/payslips/{$payslip->uuid}");
        $response2->assertStatus(403);
    }
}
