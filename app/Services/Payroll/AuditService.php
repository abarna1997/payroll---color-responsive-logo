<?php

namespace App\Services\Payroll;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log a payroll-related action.
     */
    public function log(string $action, string $module, int $recordId, ?string $oldValue = null, ?string $newValue = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
        ]);
    }
}
