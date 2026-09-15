<?php

namespace App\Services\Payroll;

use App\Models\PayrollPeriod;

class ApprovalService
{
    /**
     * Transition a payroll period to the next workflow status.
     */
    public function transition(PayrollPeriod $period, string $newStatus): bool
    {
        $allowedStatuses = ['Draft', 'Calculated', 'HR Review', 'Finance Review', 'Approved', 'Locked', 'Paid'];

        if (!in_array($newStatus, $allowedStatuses)) {
            return false;
        }

        // Lock enforcement: cannot modify Locked or Paid unless explicitly allowed by system rules
        if (in_array($period->status, ['Locked', 'Paid']) && !in_array($newStatus, ['Locked', 'Paid'])) {
            return false;
        }

        $period->status = $newStatus;
        return $period->save();
    }

    /**
     * Check if calculations are permitted for the given period.
     */
    public function canCalculate(PayrollPeriod $period): bool
    {
        return !in_array($period->status, ['Approved', 'Locked', 'Paid']);
    }
}
