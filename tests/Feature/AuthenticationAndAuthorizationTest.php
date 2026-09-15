<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class AuthenticationAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we test RBAC and Login, we need standard permissions seeded.
        // For simplicity, we just create required users.
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@123'),
            'status' => 'Active',
            'role' => 'Employee'
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'Password@123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@123'),
            'status' => 'Active',
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'WrongPassword',
        ]);

        $this->assertGuest();
    }

    public function test_disabled_account_cannot_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@123'),
            'status' => 'Inactive', // Disabled account
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'Password@123',
        ]);

        // Validation should fail or redirect back with error
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_employee_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create([
            'role' => 'Employee',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($user)->get('/'); // Admin dashboard
        // Middleware should block employee and redirect them to portal
        $response->assertStatus(403);
    }
    
    public function test_idor_prevention_for_company_isolation()
    {
        $companyA = Company::create(['company_code' => 'CA', 'company_name' => 'Company A']);
        $companyB = Company::create(['company_code' => 'CB', 'company_name' => 'Company B']);
        
        $dept = \App\Models\Department::create(['department_name' => 'IT', 'department_code' => 'IT', 'company_id' => $companyA->id]);
        $desig = \App\Models\Designation::create(['title' => 'Dev', 'designation_code' => 'DEV', 'company_id' => $companyA->id]);
        
        $adminA = User::factory()->create(['role' => 'Admin', ]);
        Employee::create(['company_id' => $companyA->id, 'user_id' => $adminA->id, 'employee_id' => 'E01', 'employee_number' => 'E01', 'first_name' => 'Admin', 'last_name' => 'A', 'department_id' => $dept->id, 'status' => 'Active']);

        $employeeB = Employee::create(['company_id' => $companyB->id, 'employee_id' => 'E02', 'employee_number' => 'E02', 'first_name' => 'Emp', 'last_name' => 'B', 'department_id' => $dept->id, 'status' => 'Active']);
        
        // Admin A tries to access Employee B's profile
        $response = $this->actingAs($adminA)->get("/employees/{$employeeB->id}");
        
        // Should be 404 because of CompanyScope (record not found for Company A admin)
        $response->assertStatus(404);
    }
}
