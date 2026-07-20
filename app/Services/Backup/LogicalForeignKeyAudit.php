<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Checks the 12 logical (application-enforced, not DB-enforced) foreign keys
 * documented in docs/04-foreign-keys.md. Native FKs are already engine-checked
 * on their own connection and don't need this — these are the ones that only
 * a coordinated backup/restore across 5 servers can silently break.
 *
 * docs/04-foreign-keys.md's "Orphan Risk" section documents that stale
 * cross-DB references are possible with no automatic cleanup; this is the
 * detector for exactly that, run right after each backup so a restore is
 * never a surprise.
 */
class LogicalForeignKeyAudit
{
    private const CHECKS = [
        ['reporting', 'report', 'userID', 'users', 'users', 'id'],
        ['reporting', 'rescue', 'caretakerID', 'users', 'users', 'id'],
        ['animals', 'animal', 'rescueID', 'reporting', 'rescue', 'id'],
        ['animals', 'animal', 'slotID', 'shelter', 'slot', 'id'],
        ['reporting', 'image', 'animalID', 'animals', 'animal', 'id'],
        ['reporting', 'image', 'clinicID', 'animals', 'clinic', 'id'],
        ['booking', 'booking', 'userID', 'users', 'users', 'id'],
        ['booking', 'transaction', 'userID', 'users', 'users', 'id'],
        ['booking', 'adoption', 'animalID', 'animals', 'animal', 'id'],
        ['booking', 'animal_booking', 'animalID', 'animals', 'animal', 'id'],
        ['booking', 'visit_list', 'userID', 'users', 'users', 'id'],
        ['booking', 'visit_list_animal', 'animalID', 'animals', 'animal', 'id'],
    ];

    /**
     * @param array<string,string> $connectionOverrides Logical connection name
     *   (reporting/booking/animals/shelter/users) => actual connection name to
     *   query against. Used by the restore drill to point checks at
     *   *_restore_test scratch databases instead of the live ones.
     * @return array<string, int|null> Label => orphan count, or null if either
     *   side's connection was unreachable (skipped rather than false-positive).
     */
    public function run(array $connectionOverrides = []): array
    {
        $results = [];

        foreach (self::CHECKS as [$conn, $table, $column, $refConn, $refTable, $refColumn]) {
            $label = "{$conn}.{$table}.{$column} -> {$refConn}.{$refTable}.{$refColumn}";
            $resolvedConn = $connectionOverrides[$conn] ?? $conn;
            $resolvedRefConn = $connectionOverrides[$refConn] ?? $refConn;

            try {
                $results[$label] = $this->countOrphans($resolvedConn, $table, $column, $resolvedRefConn, $refTable, $refColumn);
            } catch (Throwable $e) {
                $results[$label] = null;
            }
        }

        return $results;
    }

    public function hasOrphans(array $results): bool
    {
        foreach ($results as $count) {
            if (is_int($count) && $count > 0) {
                return true;
            }
        }

        return false;
    }

    private function countOrphans(string $conn, string $table, string $column, string $refConn, string $refTable, string $refColumn): int
    {
        $referencedIds = DB::connection($refConn)->table($refTable)->pluck($refColumn);

        return DB::connection($conn)->table($table)
            ->whereNotNull($column)
            ->whereNotIn($column, $referencedIds)
            ->count();
    }
}
