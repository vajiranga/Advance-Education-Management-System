<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--email= : Email address to send notification}';
    protected $description = 'Backup database and optionally upload to Google Drive';

    public function handle()
    {
        $this->info('Starting database backup...');

        try {
            // Configuration
            $googleDrivePath = 'G:/My Drive/Data backup';
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $sqlFilename = "backup_{$timestamp}.sql";
            $zipFilename = "backup_{$timestamp}.zip";

            // Temporary path for SQL creation
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempSqlPath = $tempDir . '/' . $sqlFilename;

            // Target path (Google Drive)
            if (!file_exists($googleDrivePath)) {
                mkdir($googleDrivePath, 0755, true);
            }
            $targetZipPath = $googleDrivePath . '/' . $zipFilename;

            // 1. Generate SQL Backup (to temp folder)
            $dbType = config('database.default');
            $dbConfig = config("database.connections.{$dbType}");

            if ($dbType === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    copy($dbPath, $tempSqlPath);
                    $this->info("SQLite database backed up to temp location.");
                } else {
                    $this->error("SQLite database file not found!");
                    return 1;
                }
            } elseif ($dbType === 'mysql') {
                $command = sprintf(
                    'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                    escapeshellarg($dbConfig['host']),
                    escapeshellarg($dbConfig['port'] ?? 3306),
                    escapeshellarg($dbConfig['username']),
                    escapeshellarg($dbConfig['password']),
                    escapeshellarg($dbConfig['database']),
                    escapeshellarg($tempSqlPath)
                );
                exec($command, $output, $returnCode);
                if ($returnCode !== 0) {
                    $this->error("MySQL backup failed!");
                    return 1;
                }
                $this->info("MySQL database backed up to temp location.");
            }

            // 2. Zip the SQL file
            $this->info("Compressing backup file...");
            $zip = new \ZipArchive();
            if ($zip->open($targetZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($tempSqlPath, $sqlFilename);
                $zip->close();
                $this->info("Backup compressed successfully!");
            } else {
                $this->error("Failed to create zip file!");
                return 1;
            }

            // 3. Clean up temp SQL
            if (file_exists($tempSqlPath)) {
                unlink($tempSqlPath);
            }

            // Get file size
            $fileSize = $this->formatBytes(filesize($targetZipPath));
            $this->info("Backup saved to Google Drive: {$targetZipPath}");
            $this->info("File Size: {$fileSize}");

            // 4. Clean old backups from Google Drive path
            $this->cleanOldBackups($googleDrivePath);

            // Send email notification if requested - DISABLED locally to avoid SSL errors
            // if ($email = $this->option('email')) {
            //     $this->sendEmailNotification($email, $zipFilename, $fileSize, true);
            // }

            $this->info('Backup process completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
            return 1;
        }
    }

    private function cleanOldBackups($backupDir)
    {
        $files = glob($backupDir . '/backup_*.zip');

        // Get retention months from settings (default to 6 months if not set)
        $months = \App\Models\SystemSetting::where('key', 'dataRetentionMonths')->value('value') ?? 6;
        $keepDays = (int)$months * 30; // Convert months to days

        $this->info("Cleaning old backups in Drive older than {$keepDays} days...");

        $cutoffTime = Carbon::now()->subDays($keepDays)->timestamp;

        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned {$deleted} old backup(s) from Drive.");
        } else {
            $this->info("No old backups to clean.");
        }
    }

    private function sendEmailNotification($email, $filename, $fileSize, $success, $error = null)
    {
        try {
            // 1. Configure aggressive SSL bypass via config
            config([
                'mail.mailers.smtp.stream' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]
            ]);

            // 2. Clear resolved mail instances to force reload
            if (app()->bound('mail.manager')) {
                app()->forgetInstance('mail.manager');
            }
            if (app()->bound('mailer')) {
                app()->forgetInstance('mailer');
            }

            // 3. Prepare message
            $subject = $success ? 'Database Backup Successful' : 'Database Backup Failed';
            $message = $success
                ? "Database backup completed successfully.\n\nFilename: {$filename}\nSize: {$fileSize}\nTime: " . Carbon::now()->toDateTimeString()
                : "Database backup failed.\n\nError: {$error}\nTime: " . Carbon::now()->toDateTimeString();

            // 4. Send using reloaded Facade
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                     ->subject($subject);
            });

            $this->info("Email notification sent to {$email}");
        } catch (\Exception $e) {
            $this->warn("Failed to send email: " . $e->getMessage());
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
