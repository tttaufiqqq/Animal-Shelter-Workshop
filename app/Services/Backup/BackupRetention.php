<?php

namespace App\Services\Backup;

use Carbon\Carbon;

/**
 * Keeps the last 7 daily + 4 weekly successful runs. Unlike the old
 * `find -mtime +7 -delete` script, this reads manifest status (not file age)
 * so a run of failed nights can never prune its way down to zero backups —
 * failed runs are never counted as survivors, and a floor of MIN_SURVIVORS
 * blocks deletion below that no matter what.
 */
class BackupRetention
{
    private const DAILY_KEEP = 7;
    private const WEEKLY_KEEP = 4;
    private const MIN_SURVIVORS = 3;

    public function __construct(private BackupManifest $manifests)
    {
    }

    /**
     * @return array{kept: string[], deleted: string[]}
     */
    public function prune(string $backupsRoot): array
    {
        $runs = $this->manifests->all($backupsRoot);
        $successful = array_filter($runs, fn ($m) => ($m['status'] ?? 'failed') !== 'failed');

        if (count($successful) <= self::MIN_SURVIVORS) {
            return ['kept' => array_keys($runs), 'deleted' => []];
        }

        $keep = $this->selectToKeep($successful);
        $deleted = [];

        foreach (array_keys($runs) as $runId) {
            if (!in_array($runId, $keep, true)) {
                $this->deleteRun("{$backupsRoot}/{$runId}");
                $deleted[] = $runId;
            }
        }

        return ['kept' => $keep, 'deleted' => $deleted];
    }

    /**
     * @param array<string, array> $successful
     * @return string[]
     */
    private function selectToKeep(array $successful): array
    {
        $runIds = array_keys($successful);
        rsort($runIds); // Run ids are UTC "Ymd_His" timestamps, so lexical sort is chronological.

        $daily = array_slice($runIds, 0, self::DAILY_KEEP);

        $weekly = [];
        $seenWeeks = [];

        foreach ($runIds as $runId) {
            $week = $this->parseRunTimestamp($runId)->format('o-W');

            if (!isset($seenWeeks[$week])) {
                $seenWeeks[$week] = true;
                $weekly[] = $runId;
            }

            if (count($weekly) >= self::WEEKLY_KEEP) {
                break;
            }
        }

        $keep = array_values(array_unique([...$daily, ...$weekly]));

        return count($keep) < self::MIN_SURVIVORS
            ? array_slice($runIds, 0, self::MIN_SURVIVORS)
            : $keep;
    }

    private function parseRunTimestamp(string $runId): Carbon
    {
        return Carbon::createFromFormat('Ymd_His', $runId, 'UTC');
    }

    private function deleteRun(string $runDir): void
    {
        foreach (glob("{$runDir}/*") ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($runDir);
    }
}
