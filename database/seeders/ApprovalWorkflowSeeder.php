<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Models\Role;
use App\Models\AccessLevel;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Leave Approval Workflow
        $leaveWorkflow = ApprovalWorkflow::updateOrCreate(
            ['module' => 'leave'],
            [
                'name' => 'Leave Approval Workflow',
                'description' => 'Multi-step approval chain for employee leave requests.',
                'is_active' => true,
            ]
        );

        // Fetch Manager role & access level details
        $managerRole = Role::where('name', 'Manager')->first();
        $headRole = Role::where('name', 'Department Manager')->first() ?? Role::where('name', 'HR Administrator')->first();
        
        $levelL1 = AccessLevel::where('level', 1)->first();
        $levelL2 = AccessLevel::where('level', 2)->first();
        $levelL3 = AccessLevel::where('level', 3)->first();
        $levelL4 = AccessLevel::where('level', 4)->first();

        // Step 1: Supervisor Review
        ApprovalStep::updateOrCreate(
            [
                'workflow_id' => $leaveWorkflow->id,
                'step_number' => 1,
            ],
            [
                'step_name' => 'Supervisor Review',
                'approver_type' => 'Role',
                'role_id' => $managerRole ? $managerRole->id : null,
                'access_level_id' => $levelL1 ? $levelL1->id : null,
                'auto_approve_hours' => 48,
                'is_active' => true,
            ]
        );

        // Step 2: HR Approval
        ApprovalStep::updateOrCreate(
            [
                'workflow_id' => $leaveWorkflow->id,
                'step_number' => 2,
            ],
            [
                'step_name' => 'HR Approval',
                'approver_type' => 'Role',
                'role_id' => $headRole ? $headRole->id : null,
                'access_level_id' => $levelL2 ? $levelL2->id : null,
                'auto_approve_hours' => null,
                'is_active' => true,
            ]
        );

        // Step 3: Manager Final Sign-off
        ApprovalStep::updateOrCreate(
            [
                'workflow_id' => $leaveWorkflow->id,
                'step_number' => 3,
            ],
            [
                'step_name' => 'Manager Final Sign-off',
                'approver_type' => 'Role',
                'role_id' => $headRole ? $headRole->id : null,
                'access_level_id' => $levelL4 ? $levelL4->id : null,
                'auto_approve_hours' => null,
                'is_active' => true,
            ]
        );

        // 2. Payroll Workflow
        $payrollWorkflow = ApprovalWorkflow::updateOrCreate(
            ['module' => 'payroll'],
            [
                'name' => 'Payroll Approval Workflow',
                'description' => 'Multi-step approval chain for finalizing monthly payroll periods.',
                'is_active' => true,
            ]
        );

        ApprovalStep::updateOrCreate(
            [
                'workflow_id' => $payrollWorkflow->id,
                'step_number' => 1,
            ],
            [
                'step_name' => 'Payroll Officer Review',
                'approver_type' => 'Role',
                'role_id' => $managerRole ? $managerRole->id : null,
                'access_level_id' => $levelL2 ? $levelL2->id : null,
                'is_active' => true,
            ]
        );

        ApprovalStep::updateOrCreate(
            [
                'workflow_id' => $payrollWorkflow->id,
                'step_number' => 2,
            ],
            [
                'step_name' => 'Finance Manager Approval',
                'approver_type' => 'Role',
                'role_id' => $headRole ? $headRole->id : null,
                'access_level_id' => $levelL4 ? $levelL4->id : null,
                'is_active' => true,
            ]
        );
    }
}
