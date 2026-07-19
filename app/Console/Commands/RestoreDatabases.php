<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupManifest;
use App\Services\Backup\BackupTargetResolver;
use App\Services\Backup\DatabaseRestorer;
use App\Services\Backup\LogicalForeignKeyAudit;
use Illuminate\Console\Command;

class RestoreDatabases extends Command
{
    private const SCRATCH_SUFFIX = '_restore_test';

    protected $signature = 'db:restore
        {run : The run id to restore, e.g. 20260720_020000}
        {--into-scratch : Restore into *_restore_test databases instead of the live ones}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Restore a backup run, verifying checksums first';

    public function __construct(
        private BackupTargetResolver $resolver,
        private BackupManifest $manifest,
        private DatabaseRestorer $restorer,
        private LogicalForeignKeyAudit $audit,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $runId = $this->argument('run');
        $intoScratch = (bool) $this->option('into-scratch');
        $runDir = storage_path("app/backups/{$runId}");

        $manifest = $this->manifest->read($runDir);

        if (!$manifest) {
            $this->error("No manifest found for run '{$runId}' at {$runDir}");

            return Command::FAILURE;
        }

        if (!$this->verifyChecksums($manifest, $runDir)) {
            return Command::FAILURE;
        }

        $this->info($intoScratch
            ? "Restoring run '{$runId}' into *_restore_test scratch databases."
            : "Restoring run '{$runId}' into the LIVE databases.");

        if (!$this->option('force') && !$this->confirm('Continue?')) {
            $this->info('Restore cancelled.');

            return Command::SUCCESS;
        }

        $targets = $this->resolver->targets();
        $overrides = $this->restoreAll($manifest, $targets, $runDir, $intoScratch);

        $this->info('Restore complete. Re-running logical foreign key audit...');
        $this->reportAudit($this->audit->run($overrides));

        return Command::SUCCESS;
    }

    private function verifyChecksums(array $manifest, string $runDir): bool
    {
        foreach ($manifest['targets'] as $name => $fileMeta) {
            $filePath = "{$runDir}/{$fileMeta['file']}";

            if (!is_file($filePath) || hash_file('sha256', $filePath) !== $fileMeta['sha256']) {
                $this->error("Checksum mismatch or missing file for target '{$name}' — refusing to restore.");

                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string,string> Logical connection name => scratch connection
     *   name, for pointing the post-restore audit at scratch databases. Empty
     *   when restoring live.
     */
    private function restoreAll(array $manifest, array $targets, string $runDir, bool $intoScratch): array
    {
        $overrides = [];

        foreach ($manifest['targets'] as $name => $fileMeta) {
            $target = $targets[$name] ?? null;

            if (!$target) {
                $this->warn("Skipping '{$name}': no matching connection configured anymore.");
                continue;
            }

            $database = $intoScratch ? $target['database'] . self::SCRATCH_SUFFIX : $target['database'];
            $filePath = "{$runDir}/{$fileMeta['file']}";

            $this->info("Restoring {$name} into '{$database}'...");

            if ($intoScratch) {
                $this->restorer->resetScratchDatabase($target, $database);
                $overrides = array_merge($overrides, $this->registerScratchConnections($target, $database));
            }

            $this->restorer->restore($target, $filePath, $database);
        }

        return $overrides;
    }

    /**
     * @return array<string,string>
     */
    private function registerScratchConnections(array $target, string $scratchDatabase): array
    {
        $overrides = [];

        foreach ($target['connections'] as $logicalConn) {
            $scratchName = "{$logicalConn}_scratch";

            config(["database.connections.{$scratchName}" => array_merge(
                config("database.connections.{$logicalConn}"),
                ['database' => $scratchDatabase]
            )]);

            $overrides[$logicalConn] = $scratchName;
        }

        return $overrides;
    }

    private function reportAudit(array $orphans): void
    {
        if ($this->audit->hasOrphans($orphans)) {
            $this->warn('Orphans present after restore:');

            foreach ($orphans as $label => $count) {
                if ($count) {
                    $this->warn("  - {$label}: {$count}");
                }
            }

            return;
        }

        $this->info('No logical foreign key orphans detected.');
    }
}
