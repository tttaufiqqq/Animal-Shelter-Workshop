<?php

namespace App\Services\Concerns\ForeignKeyValidator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ValidatesBatchAndCache
{
    public static function validateUsers(array $userIds): array
    {
        $userIds = array_filter($userIds);

        if (empty($userIds)) {
            return [];
        }

        $existingIds = DB::connection('users')->table('users')->whereIn('id', $userIds)->pluck('id')->toArray();

        return ['valid' => $existingIds, 'invalid' => array_diff($userIds, $existingIds)];
    }

    public static function validateAnimals(array $animalIds): array
    {
        $animalIds = array_filter($animalIds);

        if (empty($animalIds)) {
            return [];
        }

        $existingIds = DB::connection('animals')->table('animal')->whereIn('id', $animalIds)->pluck('id')->toArray();

        return ['valid' => $existingIds, 'invalid' => array_diff($animalIds, $existingIds)];
    }

    public static function clearCache(): void
    {
        Cache::flush();
    }

    public static function clearUserCache($userId): void
    {
        Cache::forget("user_exists_{$userId}");
    }

    public static function clearAnimalCache($animalId): void
    {
        Cache::forget("animal_exists_{$animalId}");
    }

    public static function clearRoleCache($roleId): void
    {
        Cache::forget("role_exists_{$roleId}");
    }

    public static function clearImageCache($imageId): void
    {
        Cache::forget("image_exists_{$imageId}");
    }

    public static function clearMedicalCache($medicalId): void
    {
        Cache::forget("medical_exists_{$medicalId}");
    }

    public static function clearVaccinationCache($vaccinationId): void
    {
        Cache::forget("vaccination_exists_{$vaccinationId}");
    }

    public static function clearInventoryCache($inventoryId): void
    {
        Cache::forget("inventory_exists_{$inventoryId}");
    }

    public static function clearAdoptionCache($adoptionId): void
    {
        Cache::forget("adoption_exists_{$adoptionId}");
    }

    public static function clearVisitListCache($visitListId): void
    {
        Cache::forget("visit_list_exists_{$visitListId}");
    }
}
