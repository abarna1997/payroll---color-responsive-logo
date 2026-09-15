<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeScheduleOverride;
use App\Models\EmployeeShiftAssignment;
use App\Models\WeeklySchedule;
use App\Services\ShiftResolverService;
use Carbon\Carbon;

class QaPhase7Test extends TestCase
{
    use RefreshDatabase;

    protected $resolver;
    protected $employee;
    protected $standardShift;
    protected $officeShift;
    protected $nightShift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ShiftResolverService();

        $this->standardShift = Shift::create([
            'shift_name' => 'Standard Staff',
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);
        
        $this->officeShift = Shift::create([
            'shift_name' => 'Office Flexible',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->nightShift = Shift::create([
            'shift_name' => 'Night Shift',
            'start_time' => '18:00:00',
            'end_time' => '06:00:00',
            'is_cross_midnight' => true,
        ]);

        $companyId = DB::table('companies')->insertGetId([
            'company_name' => 'Test Company', 'company_code' => 'TC01', 'created_at' => now(), 'updated_at' => now()
        ]);
        $deptId = DB::table('departments')->insertGetId([
            'department_name' => 'Test Dept', 'department_code' => 'TD01', 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()
        ]);
        $desigId = DB::table('designations')->insertGetId([
            'title' => 'Test Desig', 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()
        ]);

        $empId = \Illuminate\Support\Facades\DB::table('employees')->insertGetId([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'employee_number' => 100,
            'employee_id' => 'E100',
            'company_id' => $companyId,
            'department_id' => $deptId,
            'shift_id' => $this->standardShift->id, // Legacy fallback
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->employee = Employee::find($empId);
    }

    public function test_legacy_fallback_resolution()
    {
        $result = $this->resolver->resolve($this->employee, '2026-09-01');
        
        $this->assertEquals($this->standardShift->id, $result['shift']->id);
        $this->assertEquals('LEGACY', $result['source']);
    }

    public function test_assignment_resolution()
    {
        EmployeeShiftAssignment::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->officeShift->id,
            'effective_from' => '2026-09-01',
            'effective_to' => null
        ]);

        $result = $this->resolver->resolve($this->employee, '2026-09-05');
        
        $this->assertEquals($this->officeShift->id, $result['shift']->id);
        $this->assertEquals('ASSIGNMENT', $result['source']);
        $this->assertEquals('2026-09-01', $result['effective_from']);
    }

    public function test_weekly_schedule_resolution()
    {
        // Assignment is Office Flexible from 01 Sep
        EmployeeShiftAssignment::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->officeShift->id,
            'effective_from' => '2026-09-01',
            'effective_to' => null
        ]);

        // Weekly schedule overrides Assignment
        WeeklySchedule::create([
            'employee_id' => $this->employee->id,
            'effective_from' => '2026-09-01',
            'monday_shift' => $this->standardShift->id,
            'tuesday_shift' => $this->standardShift->id,
            'wednesday_shift' => $this->nightShift->id,
            'saturday_shift' => 'OFF',
            'sunday_shift' => 'OFF',
        ]);

        // Sept 1, 2026 is a Tuesday -> Standard Shift
        $result = $this->resolver->resolve($this->employee, '2026-09-01');
        $this->assertEquals($this->standardShift->id, $result['shift']->id);
        $this->assertEquals('WEEKLY', $result['source']);

        // Sept 2, 2026 is a Wednesday -> Night Shift
        $result = $this->resolver->resolve($this->employee, '2026-09-02');
        $this->assertEquals($this->nightShift->id, $result['shift']->id);
        $this->assertEquals('WEEKLY', $result['source']);

        // Sept 5, 2026 is a Saturday -> OFF
        $result = $this->resolver->resolve($this->employee, '2026-09-05');
        $this->assertNull($result['shift']);
        $this->assertEquals('WEEKLY', $result['source']);
    }

    public function test_daily_override_resolution()
    {
        // Override for Sept 15, 2026
        EmployeeScheduleOverride::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-09-15',
            'shift_id' => $this->nightShift->id,
            'reason' => 'Management Requirement'
        ]);

        // Sept 15 should be Night Shift
        $result = $this->resolver->resolve($this->employee, '2026-09-15');
        $this->assertEquals($this->nightShift->id, $result['shift']->id);
        $this->assertEquals('OVERRIDE', $result['source']);

        // Sept 16 should fall back to Legacy (since no assignment or weekly exists)
        $result = $this->resolver->resolve($this->employee, '2026-09-16');
        $this->assertEquals($this->standardShift->id, $result['shift']->id);
        $this->assertEquals('LEGACY', $result['source']);
    }

    public function test_shift_change_historical_test()
    {
        // 01 Sep: Standard Legacy (implicit)
        
        // 05 Sep: Override Office
        EmployeeScheduleOverride::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-09-05',
            'shift_id' => $this->officeShift->id,
        ]);

        // 10 Sep: Office Assignment
        EmployeeShiftAssignment::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->officeShift->id,
            'effective_from' => '2026-09-10',
            'effective_to' => null
        ]);

        // Process 01 Sep
        $res01 = $this->resolver->resolve($this->employee, '2026-09-01');
        $this->assertEquals($this->standardShift->id, $res01['shift']->id);
        $this->assertEquals('LEGACY', $res01['source']);

        // Process 05 Sep
        $res05 = $this->resolver->resolve($this->employee, '2026-09-05');
        $this->assertEquals($this->officeShift->id, $res05['shift']->id);
        $this->assertEquals('OVERRIDE', $res05['source']);

        // Process 10 Sep
        $res10 = $this->resolver->resolve($this->employee, '2026-09-10');
        $this->assertEquals($this->officeShift->id, $res10['shift']->id);
        $this->assertEquals('ASSIGNMENT', $res10['source']);
    }
}
