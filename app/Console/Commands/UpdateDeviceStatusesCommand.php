<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;

class UpdateDeviceStatusesCommand extends Command
{
    protected $signature = 'app:update-device-statuses';

    protected $description = 'Evaluate computed online/offline device statuses and persist to database';

    public function handle()
    {
        $this->info('Scanning and updating device statuses...');

        $devices = Device::all();
        $updated = 0;

        foreach ($devices as $d) {
            $computedStatus = $d->status_text;
            if ($d->status !== $computedStatus) {
                $d->status = $computedStatus;
                $d->save();
                $updated++;
            }
        }

        $this->info("Scan complete. Updated {$updated} device status(es).");

        return 0;
    }
}
