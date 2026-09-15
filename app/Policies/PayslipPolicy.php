<?php

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayslipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_payroll') || in_array($user->role, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payslip $payslip): bool
    {
        if ($user->hasPermissionTo('manage_payroll') || in_array($user->role, ['Admin', 'Super Admin'])) {
            return true;
        }

        if ($user->employee && $user->employee->id === $payslip->employee_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_payroll') || in_array($user->role, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payslip $payslip): bool
    {
        return $user->hasPermissionTo('manage_payroll') || in_array($user->role, ['Admin', 'Super Admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payslip $payslip): bool
    {
        return $user->hasPermissionTo('manage_payroll') || in_array($user->role, ['Admin', 'Super Admin']);
    }
}
