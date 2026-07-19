<?php

namespace App\Services\Backup;

use App\Services\DatabaseConnectionChecker;

/**
 * The 5 Laravel connections (reporting, booking, shelter, animals, users) map
 * to only 3 physical databases — reporting+booking share the MariaDB server,
 * shelter+animals share the MySQL server (see docs/03-db-architecture.md).
 * Dumping per Laravel connection would take 5 uncoordinated snapshots of the
 * same 3 databases; this collapses them to the 3 that actually exist.
 */
class BackupTargetResolver
{
    /**
     * @return array<string, array{driver:string,host:string,port:int,database:string,username:string,password:string,connections:string[]}>
     */
    public function targets(): array
    {
        $grouped = [];

        foreach (array_keys(DatabaseConnectionChecker::CONNECTIONS) as $connection) {
            $config = config("database.connections.{$connection}");
            $key = "{$config['driver']}:{$config['host']}:{$config['port']}:{$config['database']}";

            $grouped[$key]['driver'] ??= $config['driver'];
            $grouped[$key]['host'] ??= $config['host'];
            $grouped[$key]['port'] ??= $config['port'];
            $grouped[$key]['database'] ??= $config['database'];
            $grouped[$key]['username'] ??= $config['username'];
            $grouped[$key]['password'] ??= $config['password'];
            $grouped[$key]['connections'][] = $connection;
        }

        return $this->withNames($grouped);
    }

    /**
     * @param array<string, array{driver:string,host:string,port:int,database:string,username:string,password:string,connections:string[]}> $grouped
     * @return array<string, array{driver:string,host:string,port:int,database:string,username:string,password:string,connections:string[]}>
     */
    private function withNames(array $grouped): array
    {
        $named = [];

        foreach ($grouped as $target) {
            $named["{$target['driver']}-workshop2"] = $target;
        }

        return $named;
    }
}
