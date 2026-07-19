<?php

namespace App\Console\Commands;

use App\Mail\DatabaseBackupFailed;
use App\Services\Backup\BackupManifest;
use App\Services\Backup\BackupRetention;
use App\Services\Backup\BackupTargetResolver;
use App\Services\Backup\DatabaseDumper;
use App\Services\Backup\LogicalForeignKeyAudit;
use App\Services\DatabaseConnectionChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BackupDatabases extends Command
{
    public const STATUS_CACHE_KEY = 'backup_last_status';

    protected $signature = 'db:backup';

    protected $description = 'Take one coordinated, checksummed, integrity-verified backup of all distributed databases';

    public function __construct(
        private BackupTargetResolver $resolver,
        private DatabaseDumper $dumper,
        private BackupManifest $manifest,
        private LogicalForeignKeyAudit $audit,
        private BackupRetention $retention,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // A partial set is not a coordinated set — abort rather than write one
        // silently. See docs/10-backups.md.
        $status = app(DatabaseConnectionChecker::class)->checkAll(false);
        $offline = array_keys(array_filter($status, fn ($db) => !$db['connected']));

        if (!empty($offline)) {
            return $this->abortRun('Aborted: connection(s) offline before backup started: ' . implode(', ', $offline));
        }

        $runId = now('UTC')->format('Ymd_His');
        $backupsRoot = storage_path('app/backups');
        $runDir = "{$backupsRoot}/{$runId}";
        mkdir($runDir, 0700, true);

        $startedAt = now('UTC');
        $startedMicrotime = microtime(true);
        $files = [];

        foreach ($this->resolver->targets() as $name => $target) {
            $this->info("Dumping {$name}...");

            try {
                $files[$name] = $this->dumper->dump($name, $target, $runDir);
            } catch (Throwable $e) {
                $this->deleteDir($runDir);

                return $this->abortRun("Aborted: dump of '{$name}' failed: " . $e->getMessage());
            }
        }

        $this->info('Running logical foreign key integrity audit...');
        $orphans = $this->audit->run();
        $degraded = $this->audit->hasOrphans($orphans);

        $manifestData = [
            'run_id' => $runId,
            'status' => $degraded ? 'degraded' : 'ok',
            'started_at' => $startedAt->toIso8601String(),
            'finished_at' => now('UTC')->toIso8601String(),
            'duration_seconds' => round(microtime(true) - $startedMicrotime, 2),
            'targets' => $files,
            'orphans' => $orphans,
        ];

        $this->manifest->write($runDir, $manifestData);
        Cache::forever(self::STATUS_CACHE_KEY, $manifestData);

        $prune = $this->retention->prune($backupsRoot);
        if (!empty($prune['deleted'])) {
            $this->info('Pruned old runs: ' . implode(', ', $prune['deleted']));
        }

        if ($degraded) {
            $this->warn('Backup completed but logical foreign key orphans were detected:');
            $this->reportOrphans($orphans);
            Log::warning('Database backup completed with orphaned logical foreign keys', $manifestData);
            $this->notify($manifestData);

            return Command::SUCCESS; // Still a usable backup, just flagged.
        }

        $this->info("Backup {$runId} completed successfully.");
        Log::info('Database backup completed successfully', $manifestData);

        return Command::SUCCESS;
    }

    private function reportOrphans(array $orphans): void
    {
        foreach ($orphans as $label => $count) {
            if ($count) {
                $this->warn("  - {$label}: {$count} orphan(s)");
            }
        }
    }

    private function abortRun(string $message): int
    {
        $this->error($message);
        Log::error($message);

        $data = ['status' => 'failed', 'finished_at' => now('UTC')->toIso8601String(), 'message' => $message];
        Cache::forever(self::STATUS_CACHE_KEY, $data);
        $this->notify($data);

        return Command::FAILURE;
    }

    private function notify(array $manifestData): void
    {
        $to = config('mail.backup_alert_to');

        if (!$to) {
            return;
        }

        try {
            Mail::to($to)->send(new DatabaseBackupFailed($manifestData));
        } catch (Throwable $e) {
            Log::error('Failed to send backup alert email: ' . $e->getMessage());
        }
    }

    private function deleteDir(string $dir): void
    {
        foreach (glob("{$dir}/*") ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($dir);
    }
}
