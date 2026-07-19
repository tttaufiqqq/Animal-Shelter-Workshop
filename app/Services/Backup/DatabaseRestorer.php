<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class DatabaseRestorer
{
    public function restore(array $target, string $filePath, string $database): void
    {
        match ($target['driver']) {
            'mysql', 'mariadb' => $this->restoreMysqlFamily($target, $filePath, $database),
            'pgsql' => $this->restorePostgres($target, $filePath, $database),
            default => throw new RuntimeException("No restorer for driver '{$target['driver']}'"),
        };
    }

    /**
     * Drops and recreates the named database before restoring into it. Only
     * ever call this with a *_restore_test scratch name — never the live
     * database name.
     */
    public function resetScratchDatabase(array $target, string $database): void
    {
        match ($target['driver']) {
            'mysql', 'mariadb' => $this->resetMysqlScratch($target, $database),
            'pgsql' => $this->resetPostgresScratch($target, $database),
            default => throw new RuntimeException("No scratch reset for driver '{$target['driver']}'"),
        };
    }

    private function restoreMysqlFamily(array $target, string $filePath, string $database): void
    {
        $defaultsFile = MysqlCredentialsFile::write($target);

        try {
            $this->runMysqlStatement($defaultsFile, "CREATE DATABASE IF NOT EXISTS `{$database}`");

            $result = Process::timeout(1200)
                ->input(fopen("compress.zlib://{$filePath}", 'rb'))
                ->run(['mysql', "--defaults-extra-file={$defaultsFile}", $database]);

            if (!$result->successful()) {
                throw new RuntimeException("mysql restore failed: {$result->errorOutput()}");
            }
        } finally {
            @unlink($defaultsFile);
        }
    }

    private function resetMysqlScratch(array $target, string $database): void
    {
        $defaultsFile = MysqlCredentialsFile::write($target);

        try {
            $this->runMysqlStatement($defaultsFile, "DROP DATABASE IF EXISTS `{$database}`");
            $this->runMysqlStatement($defaultsFile, "CREATE DATABASE `{$database}`");
        } finally {
            @unlink($defaultsFile);
        }
    }

    private function runMysqlStatement(string $defaultsFile, string $sql): void
    {
        $result = Process::timeout(30)->run(['mysql', "--defaults-extra-file={$defaultsFile}", '-e', $sql]);

        if (!$result->successful()) {
            throw new RuntimeException("mysql statement failed: {$result->errorOutput()}");
        }
    }

    private function restorePostgres(array $target, string $filePath, string $database): void
    {
        $result = Process::timeout(1200)
            ->env(['PGPASSWORD' => $target['password']])
            ->run([
                'pg_restore',
                '--clean',
                '--if-exists',
                '--host=' . $target['host'],
                '--port=' . $target['port'],
                '--username=' . $target['username'],
                '--dbname=' . $database,
                $filePath,
            ]);

        if (!$result->successful()) {
            throw new RuntimeException("pg_restore failed: {$result->errorOutput()}");
        }
    }

    private function resetPostgresScratch(array $target, string $database): void
    {
        $env = ['PGPASSWORD' => $target['password']];
        $base = [
            'psql',
            '--host=' . $target['host'],
            '--port=' . $target['port'],
            '--username=' . $target['username'],
            '-d', 'postgres',
        ];

        Process::timeout(30)->env($env)->run([...$base, '-c', "DROP DATABASE IF EXISTS {$database}"]);

        $result = Process::timeout(30)->env($env)->run([...$base, '-c', "CREATE DATABASE {$database}"]);

        if (!$result->successful()) {
            throw new RuntimeException("Failed to create scratch database: {$result->errorOutput()}");
        }
    }
}
