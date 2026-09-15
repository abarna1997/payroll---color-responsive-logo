<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\WfhRequest;
use Carbon\Carbon;

class WebPunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $company = Company::create(['company_name' => 'A', 'company_code' => 'A']);
        $branch = Branch::create(['branch_name' => 'B', 'branch_code' => 'B', 'company_id' => $company->id]);
        $dept = Department::create(['department_name' => 'D', 'department_code' => 'D', 'company_id' => $company->id]);
        $shift = Shift::create(['shift_name' => 'S', 'start_time' => '09:00:00', 'end_time' => '17:00:00']);

        $this->user = User::create([
            'username' => 'testuser',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'Employee'
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $dept->id,
            'shift_id' => $shift->id,
            'employee_id' => 'EMP-001',
            'employee_number' => '1',
            'first_name' => 'Test',
            'last_name' => 'User',
            'allow_remote_punch' => true,
            'home_latitude' => 6.9271,
            'home_longitude' => 79.8612,
            'allowed_radius' => 150
        ]);
    }

    public function test_remote_punch_rejected_without_wfh_request()
    {
        $response = $this->actingAs($this->user)->postJson('/attendance/web-punch', [
            'latitude' => 6.9271,
            'longitude' => 79.8612,
            'accuracy' => 10,
            'timestamp' => Carbon::today()->format('Y-m-d') . ' 09:00:00', 'action' => 'CHECK_IN'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['error' => 'No WFH request exists for today.']);
    }

    public function test_remote_punch_rejected_when_outside_radius()
    {
        $this->actingAs($this->user);
        \Illuminate\Support\Facades\DB::table('wfh_requests')->insert([
            'employee_id' => $this->employee->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => 'APPROVED',
            'reason' => 'WFH', 'company_id' => $this->employee->company_id
        ]);

        // Coordinates far away from home
        $response = $this->actingAs($this->user)->postJson('/attendance/web-punch', [
            'latitude' => 6.8,
            'longitude' => 79.9,
            'accuracy' => 10,
            'timestamp' => Carbon::today()->format('Y-m-d') . ' 09:00:00', 'action' => 'CHECK_IN'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['error' => 'You are outside the approved WFH location.']);
    }

    public function test_remote_punch_accepted_with_wfh_request_and_valid_gps()
    {
        $this->actingAs($this->user);
        \Illuminate\Support\Facades\DB::table('wfh_requests')->insert([
            'employee_id' => $this->employee->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => 'APPROVED',
            'reason' => 'WFH', 'company_id' => $this->employee->company_id
        ]);

        $response = $this->actingAs($this->user)->postJson('/attendance/web-punch', [
            'latitude' => 6.9271,
            'longitude' => 79.8612,
            'accuracy' => 10,
            'timestamp' => Carbon::today()->format('Y-m-d') . ' 09:00:00', 'action' => 'CHECK_IN'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully Check-In via Web Punch.']);
    }
}







