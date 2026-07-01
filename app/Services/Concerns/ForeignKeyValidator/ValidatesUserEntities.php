<?php

namespace App\Services\Concerns\ForeignKeyValidator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ValidatesUserEntities
{
    public static function validateUser($userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return Cache::remember("user_exists_{$userId}", self::CACHE_DURATION, function () use ($userId) {
            return DB::connection('users')->table('users')->where('id', $userId)->exists();
        });
    }

    public static function validateAdopterProfile($adopterProfileId): bool
    {
        if (empty($adopterProfileId)) {
            return false;
        }

        return Cache::remember("adopter_profile_exists_{$adopterProfileId}", self::CACHE_DURATION, function () use ($adopterProfileId) {
            return DB::connection('users')->table('adopter_profile')->where('id', $adopterProfileId)->exists();
        });
    }

    public static function validateRole($roleId): bool
    {
        if (empty($roleId)) {
            return false;
        }

        return Cache::remember("role_exists_{$roleId}", self::CACHE_DURATION, function () use ($roleId) {
            return DB::connection('users')->table('roles')->where('id', $roleId)->exists();
        });
    }
}
