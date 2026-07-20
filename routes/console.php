<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh Database Connection Status - Every Minute
// This prevents stale cache by continuously monitoring database health
Schedule::command('db:refresh-status --silent')
    ->everyMinute()
    ->name('refresh-database-status')
    ->description('Monitor database connections and refresh cache when status changes')
    ->withoutOverlapping()
    ->runInBackground();

// Refresh Taufiq Materialized Views - Every 5 Minutes
// This keeps dashboard stats up-to-date without expensive real-time aggregations
Schedule::command('taufiq:refresh-stats')
    ->everyFiveMinutes()
    ->name('refresh-taufiq-stats')
    ->description('Refresh materialized views for user and adopter statistics')
    ->withoutOverlapping()
    ->runInBackground();

// Nightly Coordinated Database Backup - 02:00 UTC
// Dumps all 5 physical databases (one per connection), runs the logical FK
// integrity audit, prunes old runs, and alerts on failure/degradation. See
// docs/10-backups.md.
Schedule::command('db:backup')
    ->dailyAt('02:00')
    ->name('nightly-database-backup')
    ->description('Coordinated backup + integrity check of all distributed databases')
    ->withoutOverlapping()
    ->runInBackground();
