<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Backup;
use Illuminate\Console\Command;

class BackupCommand extends Command
{
    protected $signature = 'app:backup';

    protected $description = 'Trigger automated database backup using mysqldump';

    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = 'backup_'.date('Ymd_His').'.sql';
        $storagePath = storage_path('app/backups/');

        if (! file_exists($storagePath)) {
            @mkdir($storagePath, 0777, true);
        }

        $filePath = $storagePath.$filename;
        if (env('DB_CONNECTION', 'mysql') === 'sqlite') {
            $dbPath = env('DB_DATABASE', database_path('database.sqlite'));
            $resultCode = copy($dbPath, $filePath) ? 0 : 1;
        } else {
            $dumpPath = env('DB_DUMP_PATH', 'mysqldump');
            $dbName = env('DB_DATABASE', 'ams');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            
            $passwordArg = $dbPass ? "--password=\"{$dbPass}\"" : "";
            $cmd = "\"{$dumpPath}\" --user={$dbUser} {$passwordArg} {$dbName} > \"{$filePath}\"";

            $output = [];
            exec($cmd, $output, $resultCode);
        }

        // Apply Retention Policy (Delete backups older than 30 days)
        $this->applyRetentionPolicy($storagePath);

        if ($resultCode === 0 && file_exists($filePath)) {
            $backup = Backup::create([
                'backup_date' => now(),
                'backup_status' => 'Success',
                'backup_type' => 'Scheduled',
                'backup_location' => $filename,
            ]);

            AuditLog::create([
                'user_id' => null,
                'action' => 'DATABASE_BACKUP_SUCCESS',
                'module' => 'System Backups',
                'record_id' => $backup->id,
                'new_value' => json_encode($backup),
                'ip_address' => '127.0.0.1',
            ]);

            $this->info("Backup completed successfully: {$filename}");
        } else {
            $backup = Backup::create([
                'backup_date' => now(),
                'backup_status' => 'Failed',
                'backup_type' => 'Scheduled',
                'backup_location' => $filename,
            ]);

            AuditLog::create([
                'user_id' => null,
                'action' => 'DATABASE_BACKUP_FAILED',
                'module' => 'System Backups',
                'record_id' => $backup->id,
                'new_value' => json_encode($backup),
                'ip_address' => '127.0.0.1',
            ]);

            $this->error('Database backup execution failed.');

            return 1;
        }

        return 0;
    }

    protected function applyRetentionPolicy($storagePath)
    {
        $this->info('Applying 30-day retention policy...');
        
        $thirtyDaysAgo = now()->subDays(30);
        $oldBackups = Backup::where('backup_date', '<', $thirtyDaysAgo)->get();

        $deletedCount = 0;
        foreach ($oldBackups as $backup) {
            $filePath = $storagePath . $backup->backup_location;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $backup->delete();
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} old backup(s).");
        }
    }
}
