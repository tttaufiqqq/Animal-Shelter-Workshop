<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\DB;

trait MaintenanceTasks
{
    private function runAllMaintenance(): void
    {
        $this->info('📋 Running all maintenance tasks...');

        $results = DB::connection('users')->select('SELECT * FROM run_scheduled_maintenance()');

        $this->table(
            ['Task', 'Records Affected', 'Execution Time'],
            collect($results)->map(function ($result) {
                return [
                    'task' => $this->formatTaskName($result->task_name),
                    'records' => $result->records_affected,
                    'time' => $this->formatInterval($result->execution_time),
                ];
            })->toArray()
        );
    }

    private function unlockExpiredAccounts(): void
    {
        $this->info('🔓 Unlocking expired locked accounts...');

        $result = DB::connection('users')->select('SELECT * FROM unlock_expired_accounts()');
        $count = $result[0]->unlocked_count ?? 0;

        if ($count > 0) {
            $this->info("   ✓ Unlocked {$count} account(s)");
        } else {
            $this->comment('   ℹ No expired locked accounts found');
        }
    }

    private function cleanupSessions(): void
    {
        $this->info('🗑️  Cleaning up expired sessions...');

        $result = DB::connection('users')->select('SELECT * FROM cleanup_expired_sessions()');
        $count = $result[0]->deleted_count ?? 0;

        if ($count > 0) {
            $this->info("   ✓ Deleted {$count} expired session(s)");
        } else {
            $this->comment('   ℹ No expired sessions found');
        }
    }

    private function cleanupCache(): void
    {
        $this->info('🗑️  Cleaning up expired cache...');

        $result = DB::connection('users')->select('SELECT * FROM cleanup_expired_cache()');
        $count = $result[0]->deleted_count ?? 0;

        if ($count > 0) {
            $this->info("   ✓ Deleted {$count} expired cache item(s)");
        } else {
            $this->comment('   ℹ No expired cache items found');
        }
    }

    private function cleanupAuditLogs(): void
    {
        $this->info('🗑️  Cleaning up old audit logs (90+ days)...');

        $result = DB::connection('users')->select('SELECT * FROM cleanup_old_audit_logs(90)');
        $count = $result[0]->deleted_count ?? 0;

        if ($count > 0) {
            $this->info("   ✓ Deleted {$count} old audit log(s)");
        } else {
            $this->comment('   ℹ No old audit logs found');
        }
    }

    private function optimizeTables(): void
    {
        $this->info('⚡ Optimizing database tables...');

        $results = DB::connection('users')->select('SELECT * FROM optimize_database_tables()');

        foreach ($results as $result) {
            $this->info("   ✓ {$result->table_name}: {$result->operation} - {$result->status}");
        }
    }
}
