<?php

namespace App\Services\Backup;

/**
 * manifest.json is the source of truth for a backup run — not file mtimes.
 * Retention, the UI panel, and restore's checksum check all read it instead
 * of re-deriving state from the filesystem.
 */
class BackupManifest
{
    public function write(string $runDir, array $data): void
    {
        file_put_contents(
            "{$runDir}/manifest.json",
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function read(string $runDir): ?array
    {
        $path = "{$runDir}/manifest.json";

        if (!is_file($path)) {
            return null;
        }

        return json_decode(file_get_contents($path), true);
    }

    /**
     * @return array<string, array> Run id => manifest, ascending by run id (oldest first).
     */
    public function all(string $backupsRoot): array
    {
        if (!is_dir($backupsRoot)) {
            return [];
        }

        $runs = [];

        foreach (scandir($backupsRoot) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $manifest = $this->read("{$backupsRoot}/{$entry}");

            if ($manifest) {
                $runs[$entry] = $manifest;
            }
        }

        ksort($runs);

        return $runs;
    }

    public function latest(string $backupsRoot): ?array
    {
        $runs = $this->all($backupsRoot);

        return empty($runs) ? null : end($runs);
    }
}
