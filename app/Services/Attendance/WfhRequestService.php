<?php

namespace App\Services\Attendance;

use App\Models\Employee;
use App\Models\WfhRequest;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class WfhRequestService
{
    public function submitRequest(Employee $employee, string $date, int $shiftId, ?string $reason): WfhRequest
    {
        // Prevent duplicate for the same date
        if (WfhRequest::where('employee_id', $employee->id)->where('date', $date)->exists()) {
            throw new \Exception("A WFH request for this date already exists.");
        }

        $request = WfhRequest::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'shift_id' => $shiftId,
            'reason' => $reason,
            'status' => 'PENDING',
        ]);

        $this->logAudit('WFH_REQUEST_CREATED', $request->id, null, $request->status);

        return $request;
    }

    public function approveRequest(WfhRequest $request): WfhRequest
    {
        $oldStatus = $request->status;
        $request->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->logAudit('WFH_REQUEST_APPROVED', $request->id, $oldStatus, 'APPROVED');

        return $request;
    }

    public function rejectRequest(WfhRequest $request, string $rejectionReason): WfhRequest
    {
        $oldStatus = $request->status;
        $request->update([
            'status' => 'REJECTED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $rejectionReason,
        ]);

        $this->logAudit('WFH_REQUEST_REJECTED', $request->id, $oldStatus, 'REJECTED');

        return $request;
    }

    public function cancelRequest(WfhRequest $request): WfhRequest
    {
        if ($request->status !== 'PENDING') {
            throw new \Exception("Only pending requests can be cancelled.");
        }

        $oldStatus = $request->status;
        $request->update([
            'status' => 'CANCELLED',
        ]);

        $this->logAudit('WFH_REQUEST_CANCELLED', $request->id, $oldStatus, 'CANCELLED');

        return $request;
    }

    private function logAudit(string $action, int $recordId, ?string $oldValue, string $newValue): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => 'WFH Management',
            'record_id' => $recordId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'browser' => request()->header('User-Agent'),
        ]);
    }
}
