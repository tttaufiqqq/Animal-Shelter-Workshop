<?php

namespace App\Console\Commands;

use App\Services\UserViewService;
use Illuminate\Console\Command;

class RefreshUserStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taufiq:refresh-stats
                            {--force : Force refresh even if recently updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Taufiq materialized views (user stats, adopter stats)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Refreshing Taufiq materialized views...');

        $startTime = microtime(true);

        try {
            $service = new UserViewService;
            $result = $service->refreshMaterializedViews();

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("✅ {$result}");
            $this->info("⚡ Completed in {$duration}ms");

            // Display current stats
            $this->displayCurrentStats($service);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to refresh views: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * Display current statistics after refresh
     */
    protected function displayCurrentStats(UserViewService $service): void
    {
        $this->newLine();
        $this->info('📊 Current Statistics:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // User Stats
        $userStats = $service->getUserAccountStats();
        if ($userStats) {
            $this->line("👥 Total Users: {$userStats->total_users}");
            $this->line("✅ Active: {$userStats->active_users} ({$userStats->active_percentage}%)");
            $this->line("⚠️  Suspended: {$userStats->suspended_users}");
            $this->line("🔒 Locked: {$userStats->locked_users}");
            $this->line("📈 New Today: {$userStats->new_users_today}");
            $this->line("📊 New This Week: {$userStats->new_users_this_week}");
        }

        $this->newLine();

        // Adopter Stats
        $adopterStats = $service->getAdopterProfileStats();
        if ($adopterStats) {
            $this->line("🏠 Total Adopter Profiles: {$adopterStats->total_adopter_profiles}");
            $this->line("🐱 Prefer Cats: {$adopterStats->prefer_cats}");
            $this->line("🐶 Prefer Dogs: {$adopterStats->prefer_dogs}");
            $this->line("🐾 Prefer Both: {$adopterStats->prefer_both}");
            $this->line("📈 New This Month: {$adopterStats->new_profiles_this_month}");
            $this->line("✅ Completion Rate: {$adopterStats->profile_completion_rate}%");
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
