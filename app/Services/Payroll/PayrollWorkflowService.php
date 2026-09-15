<?php

namespace App\Services\Payroll;

use App\Models\PayrollPeriod;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollWorkflowService
{
    /**
     * @param PayrollPeriod $period
     * @throws ValidationException
     */
    public function submitForReview(PayrollPeriod $period): void
    {
        if (!in_array($period->status, ['Draft', 'Processed', 'Revision Required'])) {
            throw ValidationException::withMessages(['status' => 'Cannot submit for review from current status.']);
        }

        DB::transaction(function () use ($period) {
            $period->status = 'HR Review';
            $period->save();

            $this->logAudit($period, 'SUBMIT_FOR_REVIEW', 'Payroll period submitted for HR Review.');
        });
    }

    /**
     * @param PayrollPeriod $period
     * @param string $reason
     * @throws ValidationException
     */
    public function requestRevision(PayrollPeriod $period, string $reason): void
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages(['reason' => 'Revision reason is required.']);
        }

        if ($period->status !== 'HR Review') {
            throw ValidationException::withMessages(['status' => 'Can only request revision during HR Review.']);
        }

        DB::transaction(function () use ($period, $reason) {
            $period->status = 'Revision Required';
            $period->reviewed_by = Auth::id();
            $period->reviewed_at = now();
            $period->review_comment = $reason;
            $period->save();

            $this->logAudit($period, 'REQUEST_REVISION', "Revision requested: {$reason}");
        });
    }

    /**
     * @param PayrollPeriod $period
     * @param string|null $comment
     * @throws ValidationException
     */
    public function approve(PayrollPeriod $period, ?string $comment = null): void
    {
        if ($period->status !== 'HR Review') {
            throw ValidationException::withMessages(['status' => 'Can only approve during HR Review.']);
        }

        // Prevent self-approval if this user processed it
        if ($period->processed_by === Auth::id()) {
            throw ValidationException::withMessages(['approve' => 'You cannot approve a payroll period that you processed.']);
        }

        DB::transaction(function () use ($period, $comment) {
            $period->status = 'Approved';
            $period->approved_by = Auth::id();
            $period->approved_at = now();
            $period->approval_comment = $comment;
            $period->save();

            $this->logAudit($period, 'APPROVE', $comment ? "Approved: {$comment}" : 'Approved payroll period.');
        });
    }

    /**
     * @param PayrollPeriod $period
     * @param string|null $reason
     * @throws ValidationException
     */
    public function lock(PayrollPeriod $period, ?string $reason = null): void
    {
        if ($period->status !== 'Approved') {
            throw ValidationException::withMessages(['status' => 'Can only lock an Approved payroll.']);
        }

        DB::transaction(function () use ($period, $reason) {
            $period->status = 'Locked';
            $period->locked_by = Auth::id();
            $period->locked_at = now();
            $period->lock_reason = $reason;
            $period->save();

            $this->logAudit($period, 'LOCK', $reason ? "Locked: {$reason}" : 'Locked payroll period.');
        });
    }

    /**
     * Clone locked period to create a new editable revision.
     * @param PayrollPeriod $period
     * @param string $reason
     * @return PayrollPeriod
     * @throws ValidationException
     */
    public function createRevision(PayrollPeriod $period, string $reason): PayrollPeriod
    {
        if ($period->status !== 'Locked') {
            throw ValidationException::withMessages(['status' => 'Can only create a revision for a Locked payroll.']);
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages(['reason' => 'Revision reason is required.']);
        }

        return DB::transaction(function () use ($period, $reason) {
            $newPeriod = $period->replicate();
            $newPeriod->status = 'Revision Required';
            $newPeriod->parent_period_id = $period->id;
            $newPeriod->revision_number = $period->revision_number + 1;
            
            // Clear tracking fields
            $newPeriod->processed_by = null;
            $newPeriod->processed_at = null;
            $newPeriod->reviewed_by = null;
            $newPeriod->reviewed_at = null;
            $newPeriod->review_comment = null;
            $newPeriod->approved_by = null;
            $newPeriod->approved_at = null;
            $newPeriod->approval_comment = null;
            $newPeriod->locked_by = null;
            $newPeriod->locked_at = null;
            $newPeriod->lock_reason = null;
            
            $newPeriod->save();

            // Clone payslips directly to retain exact state until recalculated
            foreach ($period->payslips as $payslip) {
                $newPayslip = $payslip->replicate();
                $newPayslip->payroll_period_id = $newPeriod->id;
                $newPayslip->save();
            }

            $this->logAudit($newPeriod, 'CREATE_REVISION', "Created revision {$newPeriod->revision_number} from period {$period->id}. Reason: {$reason}");

            return $newPeriod;
        });
    }

    private function logAudit(PayrollPeriod $period, string $action, string $details): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => 'Payroll',
            'record_id' => $period->id,
            'new_value' => json_encode(['status' => $period->status, 'details' => $details]),
            'ip_address' => request()->ip(),
        ]);
    }
}
