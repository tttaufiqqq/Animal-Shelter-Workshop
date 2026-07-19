<?php

use App\Services\Backup\BackupManifest;
use App\Services\Backup\BackupRetention;
use Carbon\Carbon;

/**
 * The old per-host script pruned by file age with no success check
 * (`find -mtime +7 -delete`) — a week of failed nights could prune its way
 * down to zero backups. These tests pin the two guards that replace it:
 * a floor below which nothing is ever deleted, and failed runs never
 * counting as protected survivors.
 */
function makeBackupRun(string $root, string $runId, string $status): void
{
    $dir = "{$root}/{$runId}";
    mkdir($dir, 0777, true);
    file_put_contents("{$dir}/manifest.json", json_encode(['run_id' => $runId, 'status' => $status]));
}

function backupTestRoot(): string
{
    $root = sys_get_temp_dir() . '/backup_retention_test_' . uniqid();
    mkdir($root, 0777, true);

    return $root;
}

function cleanupBackupTestRoot(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (glob("{$dir}/*/*") ?: [] as $file) {
        @unlink($file);
    }

    foreach (glob("{$dir}/*") ?: [] as $sub) {
        @rmdir($sub);
    }

    @rmdir($dir);
}

it('does not prune anything while 3 or fewer successful runs exist', function () {
    $root = backupTestRoot();
    makeBackupRun($root, '20260101_020000', 'ok');
    makeBackupRun($root, '20260102_020000', 'ok');

    $result = (new BackupRetention(new BackupManifest()))->prune($root);

    expect($result['deleted'])->toBe([])
        ->and($result['kept'])->toHaveCount(2);

    cleanupBackupTestRoot($root);
});

it('never protects a failed run from deletion once pruning actually runs', function () {
    $root = backupTestRoot();

    foreach (range(1, 4) as $i) {
        makeBackupRun($root, sprintf('202601%02d_020000', $i), 'ok');
    }
    makeBackupRun($root, '20260105_020000', 'failed');

    $result = (new BackupRetention(new BackupManifest()))->prune($root);

    expect($result['deleted'])->toContain('20260105_020000')
        ->and(is_dir("{$root}/20260105_020000"))->toBeFalse();

    cleanupBackupTestRoot($root);
});

it('keeps at most 7 daily + 4 weekly successful runs, pruning the oldest first', function () {
    $root = backupTestRoot();
    $start = Carbon::create(2026, 1, 1, 2, 0, 0, 'UTC');

    foreach (range(0, 29) as $i) {
        makeBackupRun($root, $start->copy()->addDays($i)->format('Ymd_His'), 'ok');
    }

    $result = (new BackupRetention(new BackupManifest()))->prune($root);

    foreach (range(23, 29) as $i) {
        expect($result['kept'])->toContain($start->copy()->addDays($i)->format('Ymd_His'));
    }

    $oldest = $start->copy()->format('Ymd_His');
    expect($result['deleted'])->toContain($oldest)
        ->and(is_dir("{$root}/{$oldest}"))->toBeFalse();

    cleanupBackupTestRoot($root);
});
