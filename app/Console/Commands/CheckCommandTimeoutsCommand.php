<?php

namespace App\Console\Commands;

use App\Models\DeviceCommand;
use App\Models\DeviceEventLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckCommandTimeoutsCommand extends Command
{
    protected $signature = 'app:check-command-timeouts';

    protected $description = 'Scan dispatched device commands and mark timed out ones with retry limits';

    public function handle()
    {
        $this->info('Scanning dispatched device commands for timeouts...');

        $commands = DeviceCommand::where('status', 'sent')
            ->where('sent_at', '<', Carbon::now()->subMinutes(2))
            ->get();

        $timeouts = 0;
        $retries = 0;

        foreach ($commands as $cmd) {
            if ($cmd->retry_count < 3) {
                $cmd->retry_count += 1;
                $cmd->status = 'pending'; // retry command
                $cmd->failure_reason = 'Timed out waiting for device response. Re-queued for retry #' . $cmd->retry_count . '.';
                $cmd->save();
                $retries++;

                DeviceEventLog::create([
                    'device_id' => $cmd->device_id,
                    'event_type' => 'COMMAND_RETRY',
                    'event_message' => "Command ID {$cmd->id} ({$cmd->command}) timed out. Retry count: {$cmd->retry_count}/3. Re-queueing as pending.",
                    'severity' => 'Warning',
                ]);
            } else {
                $cmd->status = 'timeout';
                $cmd->failure_reason = 'Timed out after 3 retries. Device did not return a callback.';
                $cmd->save();
                $timeouts++;

                DeviceEventLog::create([
                    'device_id' => $cmd->device_id,
                    'event_type' => 'COMMAND_TIMEOUT',
                    'event_message' => "Command ID {$cmd->id} ({$cmd->command}) timed out after 3 retries. Marked as timeout.",
                    'severity' => 'Error',
                ]);
            }
        }

        $this->info("Scan complete. Retried: {$retries}, Timed out: {$timeouts}.");

        return 0;
    }
}
