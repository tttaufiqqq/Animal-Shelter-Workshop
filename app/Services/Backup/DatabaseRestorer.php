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
     * Resets the named scratch database before restoring into it. Only ever
     * call this with a *_restore_test scratch name — never the live database
     * name.
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

    /**
     * A no-op by design. Unlike MySQL/MariaDB, PostgreSQL has no way to scope
     * CREATE/DROP DATABASE to one specific database name — it's the global
     * CREATEDB role attribute or nothing. Rather than grant the app's regular
     * DB credential the ability to create arbitrary databases, the
     * *_restore_test database is provisioned once, out of band, by a
     * superuser (see docs/10-backups.md) — the same one-time-setup shape as
     * CLAUDE.md's Pre-Migration Checklist. The dump itself was taken with
     * `pg_dump --clean --if-exists`, and restore() below passes `--clean
     * --if-exists` to pg_restore too, so the restore itself drops and
     * recreates every object on the way in — no separate reset needed.
     */
    private function resetPostgresScratch(array $target, string $database): void
    {
        //
    }
}
