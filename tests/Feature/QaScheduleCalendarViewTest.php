<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Company;

class QaScheduleCalendarViewTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $shift;
    protected $employees;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->shift = Shift::factory()->create(['shift_name' => 'Test Shift']);
        
        $this->employees = Employee::factory()->count(3)->create([
            'company_id' => $this->company->id
        ]);
        
        // Add basic weeklies
        foreach ($this->employees as $emp) {
            \App\Models\WeeklySchedule::create([
                'employee_id' => $emp->id,
                'effective_from' => '2026-01-01', // Static past date
                'monday_shift' => $this->shift->id,
                'tuesday_shift' => $this->shift->id,
                'wednesday_shift' => $this->shift->id,
                'thursday_shift' => $this->shift->id,
                'friday_shift' => $this->shift->id,
                'saturday_shift' => 'OFF',
                'sunday_shift' => 'OFF',
            ]);
        }
    }

    public function test_overview_mode_returns_summary_events()
    {
        $response = $this->actingAs($this->user)->getJson('/schedules/fetch?start=2026-09-01&end=2026-09-07&view_mode=overview');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'view_mode',
            'summary_events'
        ]);
        
        $this->assertEquals('overview', $response->json('view_mode'));
        $this->assertNotEmpty($response->json('summary_events'));
        
        // Check if summary event has count 3 for employees
        $summary = collect($response->json('summary_events'))->firstWhere('date', '2026-09-01');
        $this->assertArrayHasKey('employee_count', $summary);
        $this->assertArrayHasKey('employees_list', $summary);
        $this->assertEquals(3, $summary['employee_count']); // 3 employees on tuesday
    }

    public function test_employees_mode_returns_individual_events_for_selected()
    {
        $selectedIds = [$this->employees[0]->id, $this->employees[1]->id];
        
        $url = '/schedules/fetch?start=2026-09-01&end=2026-09-07&view_mode=employees';
        foreach ($selectedIds as $id) {
            $url .= '&employee_ids[]=' . $id;
        }

        $response = $this->actingAs($this->user)->getJson($url);
        
        $response->assertStatus(200);
        $this->assertEquals('employees', $response->json('view_mode'));
        
        $schedules = $response->json('schedules');
        $this->assertCount(2, $schedules); // Should only have data for the 2 selected employees
        $this->assertArrayHasKey($this->employees[0]->id, $schedules);
        $this->assertArrayHasKey($this->employees[1]->id, $schedules);
        $this->assertArrayNotHasKey($this->employees[2]->id, $schedules);
    }

    public function test_employees_mode_returns_empty_when_none_selected()
    {
        $response = $this->actingAs($this->user)->getJson('/schedules/fetch?start=2026-09-01&end=2026-09-07&view_mode=employees');
        
        $response->assertStatus(200);
        $this->assertEquals('employees', $response->json('view_mode'));
        $this->assertEmpty($response->json('schedules'));
    }
}
