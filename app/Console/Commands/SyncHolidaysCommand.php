<?php

namespace App\Console\Commands;

use App\Services\HolidaySyncService;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync {--year= : The year to fetch holidays for} {--type= : Filter by type (public, bank, mercantile, poya)} {--force : Force update manual records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Sri Lankan Public, Bank, Mercantile, and Poya Holidays from the Gazette API';

    protected HolidaySyncService $syncService;

    /**
     * Create a new command instance.
     */
    public function __construct(HolidaySyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = $this->option('year') ? (int)$this->option('year') : (int)date('Y');
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info("Initializing Sri Lankan Gazette Calendar Synchronization...");
        $this->info("Year: $year" . ($type ? " | Type: $type" : "") . ($force ? " | Force Overwrite: ON" : ""));

        $stats = $this->syncService->syncHolidays($year, $type, $force);

        $this->info("Sync completed successfully!");
        $this->line(" - Added: {$stats['added']}");
        $this->line(" - Updated: {$stats['updated']}");
        $this->line(" - Skipped: {$stats['skipped']}");
        $this->line(" - Errors: {$stats['errors']}");

        return Command::SUCCESS;
    }
}
