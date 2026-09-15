<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\WfhRequest;
use App\Services\WebPunchEligibilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WfhWebPunchEligibilityTest extends TestCase
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

    public function test_employee_without_wfh_request_cannot_web_punch()
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
        $service = new WebPunchEligibilityService();
        
        $result = $service->checkEligibility($employee);
        
        $this->assertFalse($result['eligible']);
        $this->assertStringContainsString('No approved WFH request', $result['reason']);
    }

    public function test_approved_wfh_allows_web_punch()
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
        
        WfhRequest::create([
            'employee_id' => $employee->id,
            'date' => Carbon::today('Asia/Colombo')->format('Y-m-d'),
            'status' => 'APPROVED',
            'request_date' => Carbon::today('Asia/Colombo')->format('Y-m-d'),
            'reason' => 'Test',
        ]);

        $service = new WebPunchEligibilityService();
        $result = $service->checkEligibility($employee);
        
        $this->assertTrue($result['eligible']);
    }
}
