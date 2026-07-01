<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\MaintenanceFormatters;
use App\Console\Commands\Concerns\MaintenanceTasks;
use Illuminate\Console\Command;

class RunDatabaseMaintenance extends Command
{
    use MaintenanceTasks, MaintenanceFormatters;

    protected $signature = 'db:maintenance
                            {--unlock-accounts : Unlock expired locked accounts}
                            {--cleanup-sessions : Cleanup expired sessions}
                            {--cleanup-cache : Cleanup expired cache}
                            {--cleanup-audit : Cleanup old audit logs}
                            {--optimize : Optimize database tables}
                            {--all : Run all maintenance tasks}';

    protected $description = 'Run database maintenance tasks using PostgreSQL stored procedures';

    public function handle()
    {
        $this->info('🔧 Starting Database Maintenance...');
        $this->newLine();

        $runAll = $this->option('all');

        try {
            if ($runAll || !$this->hasAnyOption()) {
                $this->runAllMaintenance();
            } else {
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
}
