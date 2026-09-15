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
use App\Models\EmployeeShiftAssignment;

class QaBulkShiftAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id]);
        $this->dept = Department::factory()->create(['company_id' => $this->company->id]);
        
        $this->user = User::factory()->create(['role' => 'Super Admin']);

        $this->shift1 = Shift::factory()->create();
        $this->shift2 = Shift::factory()->create();
        
        $this->employees = Employee::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $this->dept->id,
        ]);
    }

    public function test_employee_search_and_filtering()
    {
        $response = $this->actingAs($this->user)->getJson('/schedules/bulk-assignment/employees?company_id=' . $this->company->id);
        
        $response->assertStatus(200)
                 ->assertJsonPath('total', 3);
    }

    public function test_cross_company_rejection()
    {
        $otherCompany = Company::factory()->create();
        $response = $this->actingAs($this->user)->getJson('/schedules/bulk-assignment/employees?company_id=' . $otherCompany->id);
        
        // Due to global scope / auth check, they should only see their company's employees
        $response->assertStatus(200)
                 ->assertJsonPath('total', 0);
    }

    public function test_bulk_preview_validates_dates()
    {
        $payload = [
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-15',
            'effective_to' => '2026-10-10', // Invalid (before from)
            'selection_mode' => 'employees',
            'employee_ids' => [$this->employees[0]->id]
        ];

        $response = $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/preview', $payload);
        $response->assertStatus(422);
    }

    public function test_bulk_preview_no_change()
    {
        EmployeeShiftAssignment::create([
            'employee_id' => $this->employees[0]->id,
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-01',
            'created_by' => $this->user->id
        ]);

        $payload = [
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-05',
            'selection_mode' => 'employees',
            'employee_ids' => [$this->employees[0]->id]
        ];

        $response = $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/preview', $payload);
        
        $response->assertStatus(200)
                 ->assertJsonPath('counts.no_change', 1)
                 ->assertJsonPath('counts.valid', 0);
    }

    public function test_bulk_preview_overlap_conflict()
    {
        EmployeeShiftAssignment::create([
            'employee_id' => $this->employees[0]->id,
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-01',
            'created_by' => $this->user->id
        ]);

        $payload = [
            'shift_id' => $this->shift2->id,
            'effective_from' => '2026-10-10',
            'selection_mode' => 'employees',
            'employee_ids' => [$this->employees[0]->id]
        ];

        $response = $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/preview', $payload);
        
        $response->assertStatus(200)
                 ->assertJsonPath('counts.conflict', 1);
    }

    public function test_bulk_execute_creates_assignment_and_end_dates_overlap()
    {
        $existing = EmployeeShiftAssignment::create([
            'employee_id' => $this->employees[0]->id,
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-01',
            'created_by' => $this->user->id
        ]);

        $payload = [
            'shift_id' => $this->shift2->id,
            'effective_from' => '2026-10-15',
            'selection_mode' => 'employees',
            'employee_ids' => [$this->employees[0]->id]
        ];

        $response = $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/execute', $payload);
        
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('results.created', 1)
                 ->assertJsonPath('results.conflicts', 1);

        $existing->refresh();
        $this->assertEquals('2026-10-14', $existing->effective_to->format('Y-m-d'));
        
        $this->assertDatabaseHas('employee_shift_assignments', [
            'employee_id' => $this->employees[0]->id,
            'shift_id' => $this->shift2->id,
            'effective_from' => '2026-10-15 00:00:00'
        ]);
        
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Bulk Shift Assignment Executed'
        ]);
    }

    public function test_idempotent_execution()
    {
        $payload = [
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-01',
            'selection_mode' => 'employees',
            'employee_ids' => [$this->employees[0]->id]
        ];

        $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/execute', $payload);
        
        // Execute again
        $response = $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/execute', $payload);
        
        $response->assertStatus(200)
                 ->assertJsonPath('results.created', 0)
                 ->assertJsonPath('results.no_change', 1);
                 
        $this->assertEquals(1, EmployeeShiftAssignment::where('employee_id', $this->employees[0]->id)->count());
    }

    public function test_select_all_filtered_mode()
    {
        $payload = [
            'shift_id' => $this->shift1->id,
            'effective_from' => '2026-10-01',
            'selection_mode' => 'organization', // Organization mode uses filters
            'company_id' => $this->company->id,
            'select_all_filtered' => 'true'
        ];

        $response = $this->actingAs($this->user)->postJson('/schedules/bulk-assignment/execute', $payload);
        
        $response->assertStatus(200)
                 ->assertJsonPath('results.created', 3);
    }
}
