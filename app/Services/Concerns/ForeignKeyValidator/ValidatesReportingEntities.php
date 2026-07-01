<?php

namespace App\Services\Concerns\ForeignKeyValidator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ValidatesReportingEntities
{
    public static function validateReport($reportId): bool
    {
        if (empty($reportId)) {
            return false;
        }

        return Cache::remember("report_exists_{$reportId}", self::CACHE_DURATION, function () use ($reportId) {
            return DB::connection('reporting')->table('report')->where('id', $reportId)->exists();
        });
    }

    public static function validateRescue($rescueId): bool
    {
        if (empty($rescueId)) {
            return false;
        }

        return Cache::remember("rescue_exists_{$rescueId}", self::CACHE_DURATION, function () use ($rescueId) {
            return DB::connection('reporting')->table('rescue')->where('id', $rescueId)->exists();
        });
    }

    public static function validateImage($imageId): bool
    {
        if (empty($imageId)) {
            return false;
        }

        return Cache::remember("image_exists_{$imageId}", self::CACHE_DURATION, function () use ($imageId) {
            return DB::connection('reporting')->table('image')->where('id', $imageId)->exists();
        });
    }
}
