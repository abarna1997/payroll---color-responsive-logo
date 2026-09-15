<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Backup;
use Illuminate\Console\Command;

class RestoreCommand extends Command
{
    protected $signature = 'app:restore {backup_id} {--force : Force restore without prompt}';

    protected $description = 'Restore database from a specific backup ID';

    public function handle()
    {
        $backupId = $this->argument('backup_id');
        $backup = Backup::find($backupId);

        if (!$backup) {
            $this->error("Backup with ID {$backupId} not found.");
            return 1;
        }

        if ($backup->backup_status !== 'Success') {
            $this->error("Cannot restore from a failed backup.");
            return 1;
        }

        $storagePath = storage_path('app/backups/');
        $filePath = $storagePath . $backup->backup_location;

        if (!file_exists($filePath)) {
            $this->error("Backup file not found at {$filePath}.");
            return 1;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("WARNING: This will completely overwrite the current database with the backup from {$backup->backup_date}. Do you wish to continue?")) {
                $this->info("Restore aborted.");
                return 0;
            }
        }

        $this->info("Starting database restore from {$backup->backup_location}...");

        if (env('DB_CONNECTION', 'mysql') === 'sqlite') {
            $dbPath = env('DB_DATABASE', database_path('database.sqlite'));
            $resultCode = copy($filePath, $dbPath) ? 0 : 1;
        } else {
            $mysqlPath = env('DB_MYSQL_PATH', 'mysql');
            $dbName = env('DB_DATABASE', 'ams');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            
            $passwordArg = $dbPass ? "--password=\"{$dbPass}\"" : "";
            $cmd = "\"{$mysqlPath}\" --user={$dbUser} {$passwordArg} {$dbName} < \"{$filePath}\"";

            $output = [];
            exec($cmd, $output, $resultCode);
        }

        if ($resultCode === 0) {
            AuditLog::create([
                'user_id' => null,
                'action' => 'DATABASE_RESTORE_SUCCESS',
                'module' => 'System Backups',
                'record_id' => $backup->id,
                'new_value' => json_encode(['restored_from' => $backup->backup_location]),
                'ip_address' => '127.0.0.1',
            ]);

            $this->info("Database restored successfully!");
            return 0;
        } else {
            AuditLog::create([
                'user_id' => null,
                'action' => 'DATABASE_RESTORE_FAILED',
                'module' => 'System Backups',
                'record_id' => $backup->id,
                'ip_address' => '127.0.0.1',
            ]);

            $this->error("Database restore failed. Check MySQL logs.");
            return 1;
        }
    }
}
