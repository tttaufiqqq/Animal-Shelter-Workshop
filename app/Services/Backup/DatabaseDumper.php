<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class DatabaseDumper
{
    /**
     * A "successful" dump under ~100 bytes is almost certainly an empty
     * mysqldump/pg_dump header with no actual data — a sign the dump silently
     * failed rather than proof it succeeded.
     */
    private const MIN_DUMP_BYTES = 100;

    /**
     * @return array{file:string, bytes:int, sha256:string}
     */
    public function dump(string $name, array $target, string $runDir): array
    {
        return match ($target['driver']) {
            'mysql', 'mariadb' => $this->dumpMysqlFamily($name, $target, $runDir),
            'pgsql' => $this->dumpPostgres($name, $target, $runDir),
            default => throw new RuntimeException("No dumper for driver '{$target['driver']}'"),
        };
    }

    private function dumpMysqlFamily(string $name, array $target, string $runDir): array
    {
        $path = "{$runDir}/{$name}.sql.gz";
        $defaultsFile = MysqlCredentialsFile::write($target);

        try {
            $gz = gzopen($path, 'wb9');

            // --routines: stored procedures are NOT included by mysqldump unless
            // asked for (unlike triggers) — this app relies on them heavily for
            // booking (see CLAUDE.md), so a dump without --routines silently
            // drops the schema objects that matter most for a real restore.
            $result = Process::timeout(600)->run([
                'mysqldump',
                "--defaults-extra-file={$defaultsFile}",
                '--single-transaction',
                '--routines',
                '--triggers',
                '--events',
                $target['database'],
            ], function (string $type, string $output) use ($gz) {
                if ($type === 'out') {
                    gzwrite($gz, $output);
                }
            });

            gzclose($gz);

            if (!$result->successful()) {
                @unlink($path);
                throw new RuntimeException("mysqldump exited {$result->exitCode()}: {$result->errorOutput()}");
            }
        } finally {
            @unlink($defaultsFile);
        }

        return $this->fileMeta($path);
    }

    private function dumpPostgres(string $name, array $target, string $runDir): array
    {
        // Custom format (not plain SQL) so pg_restore can do selective/parallel
        // restores; already compressed, so no gzip step needed here.
        $path = "{$runDir}/{$name}.dump";
        $fh = fopen($path, 'wb');

        $result = Process::timeout(600)
            ->env(['PGPASSWORD' => $target['password']])
            ->run([
                'pg_dump',
                '--format=custom',
                '--clean',
                '--if-exists',
                '--host=' . $target['host'],
                '--port=' . $target['port'],
                '--username=' . $target['username'],
                $target['database'],
            ], function (string $type, string $output) use ($fh) {
                if ($type === 'out') {
                    fwrite($fh, $output);
                }
            });

        fclose($fh);

        if (!$result->successful()) {
            @unlink($path);
            throw new RuntimeException("pg_dump exited {$result->exitCode()}: {$result->errorOutput()}");
        }

        return $this->fileMeta($path);
    }

    private function fileMeta(string $path): array
    {
        $bytes = filesize($path);

        if ($bytes === false || $bytes < self::MIN_DUMP_BYTES) {
            @unlink($path);
            throw new RuntimeException("Dump file '{$path}' is missing or suspiciously small ({$bytes} bytes)");
        }

        return [
            'file' => basename($path),
            'bytes' => $bytes,
            'sha256' => hash_file('sha256', $path),
        ];
    }
}
