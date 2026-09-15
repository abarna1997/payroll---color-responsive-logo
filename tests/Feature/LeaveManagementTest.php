<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $employeeUser;
    protected $managerUser;
    protected $employee;
    protected $leaveType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $company = Company::create(['company_code' => 'CA', 'company_name' => 'Company A']);
        $dept = Department::create(['department_name' => 'IT', 'department_code' => 'IT', 'company_id' => $company->id]);
        $desig = Designation::create(['title' => 'Dev', 'designation_code' => 'DEV', 'company_id' => $company->id]);
        
        $this->employeeUser = User::factory()->create(['role' => 'Employee', ]);
        $this->managerUser = User::factory()->create(['role' => 'Manager', 'status' => 'Active']);
        
        $this->employee = Employee::create([
            'company_id' => $company->id,
            'user_id' => $this->employeeUser->id,
            'employee_id' => 'E01',
            'employee_number' => 'E01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $dept->id,
            'status' => 'Active'
        ]);

        Employee::create([
            'company_id' => $company->id,
            'user_id' => $this->managerUser->id,
            'employee_id' => 'M01',
            'employee_number' => 'M01',
            'first_name' => 'Jane',
            'last_name' => 'Manager',
            'department_id' => $dept->id,
            'status' => 'Active'
        ]);

        $this->leaveType = LeaveType::create([
            'company_id' => $company->id,
            'name' => 'Annual Leave',
            'code' => 'AL',
            'is_paid' => true
        ]);
        
        LeaveBalance::create([
            'company_id' => $company->id,
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => date('Y'),
            'allocated' => 14,
            'used' => 0
        ]);
    }

    public function test_employee_can_submit_leave_request_with_sufficient_balance()
    {
        $response = $this->actingAs($this->employeeUser)->post('/portal/leave', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'Vacation'
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_employee_cannot_submit_leave_with_insufficient_balance()
    {
        $response = $this->actingAs($this->employeeUser)->post('/portal/leave', [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(20)->format('Y-m-d'), // 20 days > 14
            'reason' => 'Long Vacation'
        ]);

        $response->assertSessionHasErrors(); // Should fail validation or logic
        $this->assertDatabaseMissing('leave_requests', [
            'employee_id' => $this->employee->id,
            'reason' => 'Long Vacation'
        ]);
    }

    public function test_manager_can_approve_leave_request_and_balance_deducted()
    {
        // Setup pending leave
        $leave = LeaveRequest::create([
            'company_id' => $this->employee->company_id,
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'days' => 2,
            'reason' => 'Vacation',
            'status' => 'PENDING'
        ]);

        // Manager approves via management route
        $response = $this->actingAs($this->managerUser)->post("/leaves/request/{$leave->id}/approve");
        $response->assertRedirect();
        
        $leave->refresh();
        $this->assertEquals('APPROVED', $leave->status);
        $this->assertEquals($this->managerUser->id, $leave->approved_by);

        // Balance should be deducted
        $balance = LeaveBalance::where('employee_id', $this->employee->id)->where('leave_type_id', $this->leaveType->id)->first();
        $this->assertEquals(2, $balance->used);
    }
}
