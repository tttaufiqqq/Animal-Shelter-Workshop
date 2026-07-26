<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Uploads one run's dump files + manifest.json to Azure Blob Storage over
 * the container-scoped SAS token from Vault (azure_backup_sas_token) —
 * read/write/list/create only, no delete, so a leaked token can't destroy
 * offsite copies. Uses the Blob REST API directly (PUT Blob) instead of an
 * SDK dependency, since a SAS token already grants everything a plain HTTP
 * PUT needs.
 */
class AzureBackupSync
{
    public function __construct(
        private ?string $sasToken = null,
        private ?string $containerUrl = null,
    ) {
        $this->sasToken ??= config('azure_backup.sas_token');
        $this->containerUrl ??= config('azure_backup.container_url');
    }

    public function configured(): bool
    {
        return (bool) ($this->sasToken && $this->containerUrl);
    }

    public function sync(string $runDir, string $runId): void
    {
        foreach (glob("{$runDir}/*") ?: [] as $file) {
            $this->uploadFile($file, $runId);
        }
    }

    private function uploadFile(string $path, string $runId): void
    {
        $filename = basename($path);
        $url = "{$this->containerUrl}/{$runId}/{$filename}?{$this->sasToken}";

        $response = Http::withBody(file_get_contents($path), 'application/octet-stream')
            ->withHeaders(['x-ms-blob-type' => 'BlockBlob'])
            ->put($url);

        if ($response->failed()) {
            throw new RuntimeException(
                "Azure backup upload failed for {$filename}: HTTP {$response->status()}"
            );
        }
    }
}
