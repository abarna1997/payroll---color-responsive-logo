<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Shift;

class PhaseTwoPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup base constraints
        $company = Company::create(['company_name' => 'A', 'company_code' => 'A']);
        $branch = Branch::create(['branch_name' => 'B', 'branch_code' => 'B01', 'company_id' => $company->id]);
        $dept = Department::create(['department_name' => 'D', 'department_code' => 'D01', 'company_id' => $company->id]);
        $shift = Shift::create(['shift_name' => 'S', 'start_time' => '09:00:00', 'end_time' => '17:00:00']);

        $this->userWithEmployee = User::create(['username' => 'has_emp', 'email' => 'has@test.com', 'password' => 'p', 'role' => 'Employee']);
        Employee::create(['first_name' => 'E', 'last_name' => '1', 'company_id' => $company->id, 'branch_id' => $branch->id, 'department_id' => $dept->id, 'shift_id' => $shift->id, 'user_id' => $this->userWithEmployee->id, 'employee_number' => 'E01', 'employee_id' => 'EMP01']);

        $this->userWithoutEmployee = User::create(['username' => 'no_emp', 'email' => 'no@test.com', 'password' => 'p', 'role' => 'Manager']);
    }

    public function test_user_without_employee_profile_cannot_access_portal()
    {
        $this->actingAs($this->userWithoutEmployee);
        
        $response = $this->get('/portal/dashboard');
        $response->assertStatus(403);
    }

    public function test_user_with_employee_profile_can_access_portal()
    {
        $this->actingAs($this->userWithEmployee);
        
        $response = $this->get('/portal/dashboard');
        $response->assertStatus(200);
    }
}
