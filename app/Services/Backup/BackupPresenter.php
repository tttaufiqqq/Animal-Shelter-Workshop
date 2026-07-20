<?php

namespace App\Services\Backup;

use Carbon\Carbon;

/**
 * Translates the raw, technical shapes BackupTargetResolver/LogicalForeignKeyAudit
 * produce (target keys like "mariadb-reporting-workshop2", orphan-check labels like
 * "reporting.report.userID -> users.users.id") into plain language for the
 * admin/backups/index view — built for a non-technical shelter admin, not a developer.
 * Every mapping falls back to a readable guess rather than raising, since backup
 * history can span old runs from before a naming scheme changed (see
 * BackupTargetResolver's docblock).
 */
class BackupPresenter
{
    private const TARGET_LABELS = [
        'reporting' => ['label' => 'Stray Reports', 'icon' => '📋'],
        'booking' => ['label' => 'Bookings & Adoptions', 'icon' => '📅'],
        'shelter' => ['label' => 'Shelter Inventory', 'icon' => '📦'],
        'animals' => ['label' => 'Animal Records', 'icon' => '🐾'],
    ];

    private const ORPHAN_LABELS = [
        'reporting.report.userID -> users.users.id' => 'Stray reports are linked to real user accounts',
        'reporting.rescue.caretakerID -> users.users.id' => 'Rescue tasks are linked to real caretakers',
        'animals.animal.rescueID -> reporting.rescue.id' => 'Animal records are linked to real rescues',
        'animals.animal.slotID -> shelter.slot.id' => 'Animals are linked to real shelter slots',
        'reporting.image.animalID -> animals.animal.id' => 'Uploaded photos are linked to real animals',
        'reporting.image.clinicID -> animals.clinic.id' => 'Uploaded photos are linked to real clinics',
        'booking.booking.userID -> users.users.id' => 'Bookings are linked to real user accounts',
        'booking.transaction.userID -> users.users.id' => 'Payments are linked to real user accounts',
        'booking.adoption.animalID -> animals.animal.id' => 'Adoption records are linked to real animals',
        'booking.animal_booking.animalID -> animals.animal.id' => 'Booked animals are linked to real animal records',
        'booking.visit_list.userID -> users.users.id' => 'Visit lists are linked to real user accounts',
        'booking.visit_list_animal.animalID -> animals.animal.id' => 'Visit-list animals are linked to real animal records',
    ];

    private const STATUS_META = [
        'ok' => [
            'heading' => 'Your data is safely backed up',
            'badge' => 'All good',
            'color' => 'green',
            'icon' => '✅',
        ],
        'degraded' => [
            'heading' => 'Backup completed, but a few things need a look',
            'badge' => 'Needs review',
            'color' => 'yellow',
            'icon' => '⚠️',
        ],
        'failed' => [
            'heading' => 'The last backup did not complete',
            'badge' => 'Failed',
            'color' => 'red',
            'icon' => '❌',
        ],
    ];

    /**
     * @return array{label: string, icon: string}
     */
    public static function targetLabel(string $targetKey): array
    {
        foreach (self::TARGET_LABELS as $connection => $meta) {
            if (str_contains($targetKey, $connection)) {
                return $meta;
            }
        }

        if (str_starts_with($targetKey, 'pgsql')) {
            return ['label' => 'User Accounts', 'icon' => '👤'];
        }

        // Historical runs from before the 2026-07-20 1-database-1-physical-machine
        // split (see BackupTargetResolver) had one combined target per driver.
        if (str_starts_with($targetKey, 'mariadb')) {
            return ['label' => 'Reports & Bookings', 'icon' => '📋'];
        }

        if (str_starts_with($targetKey, 'mysql')) {
            return ['label' => 'Shelter & Animal Records', 'icon' => '📦'];
        }

        return ['label' => ucwords(str_replace(['-', '_'], ' ', $targetKey)), 'icon' => '🗄️'];
    }

    public static function orphanLabel(string $rawLabel): string
    {
        return self::ORPHAN_LABELS[$rawLabel]
            ?? ucfirst(str_replace(['.', '->'], [' ', 'should link to'], $rawLabel));
    }

    /**
     * @return array{heading: string, badge: string, color: string, icon: string}
     */
    public static function statusMeta(string $status): array
    {
        return self::STATUS_META[$status] ?? self::STATUS_META['failed'];
    }

    public static function friendlyDate(?string $isoOrRunId): string
    {
        if (!$isoOrRunId) {
            return 'Unknown';
        }

        try {
            $date = preg_match('/^\d{8}_\d{6}$/', $isoOrRunId)
                ? Carbon::createFromFormat('Ymd_His', $isoOrRunId, 'UTC')
                : Carbon::parse($isoOrRunId);

            return $date->timezone(config('app.timezone'))->format('M j, Y \a\t g:i A');
        } catch (\Throwable) {
            return $isoOrRunId;
        }
    }

    public static function friendlyDuration(float|int|null $seconds): string
    {
        if ($seconds === null) {
            return 'Unknown';
        }

        if ($seconds < 1) {
            return 'Under a second';
        }

        if ($seconds < 60) {
            return round($seconds, 1) . ' seconds';
        }

        return round($seconds / 60, 1) . ' minutes';
    }

    public static function friendlySize(int|float $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes) . ' bytes';
    }

    public static function totalBytes(array $targets): int
    {
        return (int) collect($targets)->sum(fn ($meta) => $meta['bytes'] ?? 0);
    }

    public static function orphanIssueCount(array $orphans): int
    {
        return collect($orphans)->filter(fn ($count) => $count)->count();
    }
}
