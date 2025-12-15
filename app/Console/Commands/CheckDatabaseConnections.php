<?php

namespace App\Console\Commands;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Console\Command;

class CheckDatabaseConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-connections {--fresh : Force fresh check without cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all distributed database connections and display their status';

    /**
     * Database connection checker service
     *
     * @var DatabaseConnectionChecker
     */
    protected $checker;

    /**
     * Create a new command instance.
     */
    public function __construct(DatabaseConnectionChecker $checker)
    {
        parent::__construct();
        $this->checker = $checker;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $useFresh = $this->option('fresh');

        if ($useFresh) {
            $this->checker->clearCache();
        }

        // Display the connection status
        $this->line($this->checker->getCliOutput());

        $disconnected = $this->checker->getDisconnected();

        if (!empty($disconnected)) {
            $this->newLine();
            $this->warn('💡 TIP: To connect to remote databases, ensure:');
            $this->info('   1. SSH tunnels are active to group members\' machines');
            $this->info('   2. Database services are running on remote machines');
            $this->info('   3. Firewall rules allow connections on specified ports');
            $this->newLine();
            $this->info('✓ Server will start in offline mode. The application will work with limited functionality.');
        }

        // Always return SUCCESS - offline databases are expected, not an error
        return Command::SUCCESS;
    }
}
