<?php

namespace Tests\Feature\Payroll;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Services\Payroll\PayrollWorkflowService;

class QaPayrollWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->companyId = 1;
        
        $this->creator = User::factory()->create();
        $this->creatorEmployee = Employee::factory()->create(['user_id' => $this->creator->id, 'company_id' => $this->companyId]);
        
        $this->approver = User::factory()->create();
        $this->approverEmployee = Employee::factory()->create(['user_id' => $this->approver->id, 'company_id' => $this->companyId]);

        $this->period = PayrollPeriod::create([
            'period_name' => 'Test Period',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'cycle_type' => 'Monthly',
            'status' => 'Draft',
            'company_id' => $this->companyId
        ]);
        
        $this->workflow = new PayrollWorkflowService();
    }

    public function test_draft_to_locked_is_blocked()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->actingAs($this->approver);
        $this->workflow->lock($this->period, 'Locking directly');
    }
    
    public function test_submit_for_review_changes_state()
    {
        $this->actingAs($this->creator);
        $this->workflow->submitForReview($this->period);
        $this->assertEquals('HR Review', $this->period->fresh()->status);
    }
    
    public function test_self_approval_is_blocked()
    {
        $this->actingAs($this->creator);
        // Pretend this user processed it
        $this->period->update(['processed_by' => $this->creator->id, 'status' => 'HR Review']);
        
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('You cannot approve a payroll period that you processed.');
        $this->workflow->approve($this->period, 'Looks good');
    }

    public function test_authorized_approval_succeeds()
    {
        $this->period->update(['processed_by' => $this->creator->id, 'status' => 'HR Review']);
        
        $this->actingAs($this->approver);
        $this->workflow->approve($this->period, 'Approved by HR');
        
        $fresh = $this->period->fresh();
        $this->assertEquals('Approved', $fresh->status);
        $this->assertEquals($this->approver->id, $fresh->approved_by);
    }

    public function test_recalculate_locked_period_is_blocked()
    {
        $this->period->update(['status' => 'Locked']);
        $employee = Employee::factory()->create(['company_id' => $this->companyId]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot recalculate payroll for a Locked period.');
        
        app(\App\Services\Payroll\PayrollCalculationService::class)->calculate($this->period, $employee);
    }

    public function test_revision_creates_cloned_period()
    {
        $this->period->update(['status' => 'Locked']);
        $payslip = Payslip::create([
            'payroll_period_id' => $this->period->id,
            'employee_id' => $this->creatorEmployee->id,
            'company_id' => $this->companyId,
            'basic_salary' => 50000,
            'net_salary' => 50000,
        ]);

        $this->actingAs($this->approver);
        $newPeriod = $this->workflow->createRevision($this->period, 'Need to correct attendance');
        
        $this->assertEquals('Revision Required', $newPeriod->status);
        $this->assertEquals($this->period->id, $newPeriod->parent_period_id);
        $this->assertEquals(1, $newPeriod->revision_number);
        $this->assertEquals(1, $newPeriod->payslips()->count());
        
        // Old period still intact
        $this->assertEquals('Locked', $this->period->fresh()->status);
    }
}
