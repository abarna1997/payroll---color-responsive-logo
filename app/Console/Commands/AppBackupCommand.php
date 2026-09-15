<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use ZipArchive;

class AppBackupCommand extends Command
{
    protected $signature = 'app:backup-files';

    protected $description = 'Backup application storage files (images, documents, payslips)';

    public function handle()
    {
        $this->info('Starting application files backup...');

        $filename = 'app_backup_'.date('Ymd_His').'.zip';
        $storagePath = storage_path('app/backups/');
        $filePath = $storagePath . $filename;

        if (! file_exists($storagePath)) {
            @mkdir($storagePath, 0777, true);
        }

        $sourcePath = storage_path('app/public');
        
        if (!file_exists($sourcePath)) {
            $this->error('Storage public directory not found. Nothing to backup.');
            return 1;
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourcePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePathAbsolute = $file->getRealPath();
                    $relativePath = substr($filePathAbsolute, strlen($sourcePath) + 1);
                    $zip->addFile($filePathAbsolute, $relativePath);
                }
            }

            $zip->close();

            AuditLog::create([
                'user_id' => null,
                'action' => 'APP_FILES_BACKUP_SUCCESS',
                'module' => 'System Backups',
                'new_value' => json_encode(['file' => $filename]),
                'ip_address' => '127.0.0.1',
            ]);

            $this->info("Application backup completed successfully: {$filename}");
            return 0;
        } else {
            AuditLog::create([
                'user_id' => null,
                'action' => 'APP_FILES_BACKUP_FAILED',
                'module' => 'System Backups',
                'ip_address' => '127.0.0.1',
            ]);

            $this->error("Failed to create zip file.");
            return 1;
        }
    }
}
