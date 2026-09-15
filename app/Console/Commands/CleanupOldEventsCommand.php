<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\DeviceEventLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupOldEventsCommand extends Command
{
    protected $signature = 'app:cleanup-old-events';

    protected $description = 'Clean up old device event logs and audit trail records';

    public function handle()
    {
        $this->info('Starting system data retention cleanup...');

        // 1. Soft delete device events older than 30 days
        $softDeleted = DeviceEventLog::where('created_at', '<', Carbon::now()->subDays(30))->delete();

        // 2. Permanently purge soft-deleted events older than 60 days
        $purgedEvents = DeviceEventLog::onlyTrashed()
            ->where('created_at', '<', Carbon::now()->subDays(60))
            ->forceDelete();

        // 3. Permanently purge audit logs older than 365 days
        $purgedAudits = AuditLog::where('created_at', '<', Carbon::now()->subDays(365))->delete();

        $this->info('Cleanup completed.');
        $this->info("- Soft-deleted Device Events (>30d): {$softDeleted}");
        $this->info("- Permanently purged Device Events (>60d): {$purgedEvents}");
        $this->info("- Permanently purged Audit Logs (>365d): {$purgedAudits}");

        return 0;
    }
}
