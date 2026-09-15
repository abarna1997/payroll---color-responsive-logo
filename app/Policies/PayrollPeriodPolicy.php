<?php

namespace App\Policies;

use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollPeriodPolicy
{
    /**
     * Bypass isolation check for Admins and Super Administrators
     */
    public function before(User $user, string $ability): bool|null
    {
        $adminRoles = ['Admin', 'Super Admin', 'Super Administrator', 'HR Administrator'];
        if (in_array($user->role, $adminRoles) || $user->isSystemAccount()) {
            if ($ability === 'approve') {
                return null; // Let approve() evaluate self-approval rule
            }
            return true;
        }
        return null;
    }

    /**
     * Common isolation check - users can only interact with their company's payrolls.
     */
    private function checkIsolation(User $user, PayrollPeriod $payrollPeriod): bool
    {
        $adminRoles = ['Admin', 'Super Admin', 'Super Administrator', 'HR Administrator'];
        if (in_array($user->role, $adminRoles) || $user->isSystemAccount()) {
            return true;
        }

        // If payroll period is global (no company), allow access
        if (!$payrollPeriod->company_id) {
            return true;
        }

        if (!$user->employee) {
            return true;
        }

        return $user->employee->company_id == $payrollPeriod->company_id;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->checkIsolation($user, $payrollPeriod);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can process the model.
     */
    public function process(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->checkIsolation($user, $payrollPeriod);
    }

    /**
     * Determine whether the user can review the model.
     */
    public function review(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->checkIsolation($user, $payrollPeriod);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PayrollPeriod $payrollPeriod): Response
    {
        if (!$this->checkIsolation($user, $payrollPeriod)) {
            return Response::deny('You do not have access to this company payroll.');
        }

        if ($payrollPeriod->processed_by === $user->id && !in_array($user->role, ['Super Admin', 'Super Administrator'])) {
            return Response::deny('You cannot approve a payroll period that you processed.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can lock the model.
     */
    public function lock(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->checkIsolation($user, $payrollPeriod);
    }

    /**
     * Determine whether the user can revise the model.
     */
    public function revise(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->checkIsolation($user, $payrollPeriod);
    }
}
