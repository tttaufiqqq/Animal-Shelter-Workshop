<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateUserViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taufiq:migrate-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Taufiq high-performance views migration (bypasses other pending migrations)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Creating Taufiq high-performance views...');
        $this->newLine();

        try {
            // Check if already migrated
            $migrationName = '2026_01_04_000001_add_taufiq_high_performance_views';
            $exists = DB::connection('users')->table('migrations')
                ->where('migration', $migrationName)
                ->exists();

            if ($exists) {
                $this->warn('⚠️  Views already created! Use --fresh to recreate.');
                return Command::SUCCESS;
            }

            // Include the migration file
            $migrationPath = database_path('migrations/2026_01_04_000001_add_taufiq_high_performance_views.php');

            if (!file_exists($migrationPath)) {
                $this->error('❌ Migration file not found!');
                return Command::FAILURE;
            }

            $migration = include $migrationPath;

            $this->info('1️⃣  Creating v_user_full_profile...');
            $this->info('2️⃣  Creating v_user_account_stats (materialized)...');
            $this->info('3️⃣  Creating v_adopter_profile_stats (materialized)...');
            $this->info('4️⃣  Creating v_high_risk_users...');
            $this->info('5️⃣  Creating v_active_users_with_profiles...');
            $this->info('6️⃣  Creating v_user_activity_last_30_days...');
            $this->newLine();

            // Run the migration
            $migration->up();

            // Record migration
            DB::connection('users')->table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => DB::connection('users')->table('migrations')->max('batch') + 1,
            ]);

            $this->info('✅ All views created successfully!');
            $this->newLine();

            // Initial refresh of materialized views
            $this->info('🔄 Refreshing materialized views...');
            DB::connection('users')->select('SELECT refresh_all_taufiq_stats()');
            $this->info('✅ Materialized views refreshed!');
            $this->newLine();

            $this->info('🎉 Done! Views are ready to use.');
            $this->info('💡 Use UserViewService to access the views in your code.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->newLine();
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
