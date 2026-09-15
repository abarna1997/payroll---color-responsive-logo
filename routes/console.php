<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:check-command-timeouts')->everyMinute();
Schedule::command('app:update-device-statuses')->everyMinute();
Schedule::command('app:cleanup-old-events')->daily();
Schedule::command('app:backup')->dailyAt('01:00');

// Sync holidays daily for current and upcoming years
Schedule::command('holidays:sync')->dailyAt('02:00');
Schedule::command('holidays:sync --year=' . (date('Y') + 1))->dailyAt('02:15');
