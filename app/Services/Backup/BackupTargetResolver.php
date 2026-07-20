<?php

namespace App\Services\Backup;

use App\Services\DatabaseConnectionChecker;

/**
 * Groups the 5 Laravel connections (reporting, booking, shelter, animals,
 * users) by their physical driver+host+port+database, so that if two
 * connections ever again share one physical database (as reporting+booking
 * and shelter+animals did before the 2026-07-20 1-database-1-physical-machine
 * split — see CLAUDE.md's Server Topology table), backups collapse to one
 * dump per physical database instead of taking uncoordinated duplicate
 * snapshots of the same data.
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
        // Two physical databases can share a driver (e.g. shelter's linux-mysql
        // and animals' linux-mysql-2 are both `mysql`) — naming by driver alone
        // would collide and silently drop one target. Disambiguate with the
        // group's first connection name whenever a driver appears more than once.
        $countByDriver = [];
        foreach ($grouped as $target) {
            $countByDriver[$target['driver']] = ($countByDriver[$target['driver']] ?? 0) + 1;
        }

        $named = [];

        foreach ($grouped as $target) {
            $name = $countByDriver[$target['driver']] > 1
                ? "{$target['driver']}-{$target['connections'][0]}-workshop2"
                : "{$target['driver']}-workshop2";

            $named[$name] = $target;
        }

        return $named;
    }
}
