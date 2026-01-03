<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\BackupController;
use App\Models\BackupLog;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh Database Connection Status - Every Minute
// This prevents stale cache by continuously monitoring database health
Schedule::command('db:refresh-status --silent')
    ->everyMinute()
    ->name('refresh-database-status')
    ->description('Monitor database connections and refresh cache when status changes')
    ->withoutOverlapping()
    ->runInBackground();

// Refresh Taufiq Materialized Views - Every 5 Minutes
// This keeps dashboard stats up-to-date without expensive real-time aggregations
Schedule::command('taufiq:refresh-stats')
    ->everyFiveMinutes()
    ->name('refresh-taufiq-stats')
    ->description('Refresh materialized views for user and adopter statistics')
    ->withoutOverlapping()
    ->runInBackground();

// Weekly Database Backup - Every Sunday at 12:00 AM (Midnight)
Schedule::call(function () {
    $connections = [
        'eilya' => ['driver' => 'mysql', 'port' => 3307],
        'atiqah' => ['driver' => 'mysql', 'port' => 3308],
        'shafiqah' => ['driver' => 'mysql', 'port' => 3309],
        'danish' => ['driver' => 'sqlsrv', 'port' => 1434],
        'taufiq' => ['driver' => 'pgsql', 'port' => 5434],
    ];

    foreach ($connections as $connection => $config) {
        try {
            $startTime = now();
            $timestamp = now()->format('Y-m-d_His');
            $backupPath = storage_path("app/backups/{$connection}");

            // Create backup directory if it doesn't exist
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $backupFile = "{$backupPath}/{$timestamp}.sql";

            // Create backup log entry
            $log = BackupLog::create([
                'connection_name' => $connection,
                'backup_type' => 'scheduled',
                'status' => 'running',
                'backup_started_at' => $startTime,
                'admin_id' => null, // Scheduled backup has no admin
            ]);

            // Get database credentials
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $database = config("database.connections.{$connection}.database");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");

            // Execute backup based on driver
            if ($config['driver'] === 'mysql') {
                $command = sprintf(
                    'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($backupFile)
                );
                exec($command, $output, $returnCode);
            } elseif ($config['driver'] === 'pgsql') {
                putenv("PGPASSWORD={$password}");
                $command = sprintf(
                    'pg_dump -h %s -p %s -U %s -d %s -F p -f %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    escapeshellarg($backupFile)
                );
                exec($command, $output, $returnCode);
                putenv("PGPASSWORD");
            } elseif ($config['driver'] === 'sqlsrv') {
                $command = sprintf(
                    'sqlcmd -S %s,%s -U %s -P %s -Q "BACKUP DATABASE [%s] TO DISK = \'%s\' WITH FORMAT" 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    $database,
                    $backupFile
                );
                exec($command, $output, $returnCode);
            }

            if (isset($returnCode) && $returnCode === 0) {
                $endTime = now();
                $fileSize = file_exists($backupFile) ? filesize($backupFile) : 0;

                $log->update([
                    'status' => 'success',
                    'file_path' => $backupFile,
                    'file_size' => $fileSize,
                    'backup_completed_at' => $endTime,
                    'duration_seconds' => $endTime->diffInSeconds($startTime),
                ]);

                \Log::info("Scheduled backup successful for {$connection}: {$backupFile}");
            } else {
                $log->update([
                    'status' => 'failed',
                    'error_message' => isset($output) ? implode("\n", $output) : 'Unknown error',
                    'backup_completed_at' => now(),
                ]);

                \Log::error("Scheduled backup failed for {$connection}: " . (isset($output) ? implode("\n", $output) : 'Unknown error'));
            }

        } catch (\Exception $e) {
            \Log::error("Scheduled backup error for {$connection}: " . $e->getMessage());

            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'backup_completed_at' => now(),
                ]);
            }
        }
    }
})->weekly()->sundays()->at('00:00')->name('weekly-database-backup')->description('Backup all 5 databases every Sunday at midnight');
