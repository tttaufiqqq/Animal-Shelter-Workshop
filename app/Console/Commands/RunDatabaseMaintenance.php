<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunDatabaseMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:maintenance
                            {--unlock-accounts : Unlock expired locked accounts}
                            {--cleanup-sessions : Cleanup expired sessions}
                            {--cleanup-cache : Cleanup expired cache}
                            {--cleanup-audit : Cleanup old audit logs}
                            {--optimize : Optimize database tables}
                            {--all : Run all maintenance tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database maintenance tasks using PostgreSQL stored procedures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Starting Database Maintenance...');
        $this->newLine();

        $runAll = $this->option('all');

        try {
            // Run all maintenance tasks if --all flag is set or no specific flags
            if ($runAll || !$this->hasAnyOption()) {
                $this->runAllMaintenance();
            } else {
                // Run individual tasks based on flags
                if ($this->option('unlock-accounts')) {
                    $this->unlockExpiredAccounts();
                }

                if ($this->option('cleanup-sessions')) {
                    $this->cleanupSessions();
                }

                if ($this->option('cleanup-cache')) {
                    $this->cleanupCache();
                }

                if ($this->option('cleanup-audit')) {
                    $this->cleanupAuditLogs();
                }

                if ($this->option('optimize')) {
                    $this->optimizeTables();
                }
            }

            $this->newLine();
            $this->info('✅ Database maintenance completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error during maintenance: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Run all maintenance tasks using the combined stored procedure
     */
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

    /**
     * Unlock expired locked accounts
     */
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

    /**
     * Cleanup expired sessions
     */
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

    /**
     * Cleanup expired cache
     */
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

    /**
     * Cleanup old audit logs (90 days retention)
     */
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

    /**
     * Optimize database tables
     */
    private function optimizeTables(): void
    {
        $this->info('⚡ Optimizing database tables...');

        $results = DB::connection('users')->select('SELECT * FROM optimize_database_tables()');

        foreach ($results as $result) {
            $this->info("   ✓ {$result->table_name}: {$result->operation} - {$result->status}");
        }
    }

    /**
     * Check if any specific option is set
     */
    private function hasAnyOption(): bool
    {
        return $this->option('unlock-accounts')
            || $this->option('cleanup-sessions')
            || $this->option('cleanup-cache')
            || $this->option('cleanup-audit')
            || $this->option('optimize');
    }

    /**
     * Format task name for display
     */
    private function formatTaskName(string $taskName): string
    {
        return ucwords(str_replace('_', ' ', $taskName));
    }

    /**
     * Format PostgreSQL interval for display
     */
    private function formatInterval(string $interval): string
    {
        // PostgreSQL intervals are in format like "00:00:00.123456"
        return $interval;
    }
}
