<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ForeignKeyValidator
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    const CACHE_DURATION = 300;

    /**
     * ========================================
     * TAUFIQ'S DATABASE (User Management)
     * ========================================
     */

    /**
     * Validate if a user exists in Taufiq's database
     */
    public static function validateUser($userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return Cache::remember("user_exists_{$userId}", self::CACHE_DURATION, function () use ($userId) {
            return DB::connection('users')
                ->table('users')
                ->where('id', $userId)
                ->exists();
        });
    }

    /**
     * Validate if an adopter profile exists in Taufiq's database
     */
    public static function validateAdopterProfile($adopterProfileId): bool
    {
        if (empty($adopterProfileId)) {
            return false;
        }

        return Cache::remember("adopter_profile_exists_{$adopterProfileId}", self::CACHE_DURATION, function () use ($adopterProfileId) {
            return DB::connection('users')
                ->table('adopter_profile')
                ->where('id', $adopterProfileId)
                ->exists();
        });
    }

    /**
     * Validate if a role exists in Taufiq's database
     */
    public static function validateRole($roleId): bool
    {
        if (empty($roleId)) {
            return false;
        }

        return Cache::remember("role_exists_{$roleId}", self::CACHE_DURATION, function () use ($roleId) {
            return DB::connection('users')
                ->table('roles')
                ->where('id', $roleId)
                ->exists();
        });
    }

    /**
     * ========================================
     * EILYA'S DATABASE (Reporting & Rescue)
     * ========================================
     */

    /**
     * Validate if a report exists in Eilya's database
     */
    public static function validateReport($reportId): bool
    {
        if (empty($reportId)) {
            return false;
        }

        return Cache::remember("report_exists_{$reportId}", self::CACHE_DURATION, function () use ($reportId) {
            return DB::connection('reporting')
                ->table('report')
                ->where('id', $reportId)
                ->exists();
        });
    }

    /**
     * Validate if a rescue exists in Eilya's database
     */
    public static function validateRescue($rescueId): bool
    {
        if (empty($rescueId)) {
            return false;
        }

        return Cache::remember("rescue_exists_{$rescueId}", self::CACHE_DURATION, function () use ($rescueId) {
            return DB::connection('reporting')
                ->table('rescue')
                ->where('id', $rescueId)
                ->exists();
        });
    }

    /**
     * Validate if an image exists in Eilya's database
     */
    public static function validateImage($imageId): bool
    {
        if (empty($imageId)) {
            return false;
        }

        return Cache::remember("image_exists_{$imageId}", self::CACHE_DURATION, function () use ($imageId) {
            return DB::connection('reporting')
                ->table('image')
                ->where('id', $imageId)
                ->exists();
        });
    }

    /**
     * ========================================
     * ATIQAH'S DATABASE (Inventory Management)
     * ========================================
     */

    /**
     * Validate if a slot exists in Atiqah's database
     */
    public static function validateSlot($slotId): bool
    {
        if (empty($slotId)) {
            return false;
        }

        return Cache::remember("slot_exists_{$slotId}", self::CACHE_DURATION, function () use ($slotId) {
            return DB::connection('shelter')
                ->table('slot')
                ->where('id', $slotId)
                ->exists();
        });
    }

    /**
     * Validate if a section exists in Atiqah's database
     */
    public static function validateSection($sectionId): bool
    {
        if (empty($sectionId)) {
            return false;
        }

        return Cache::remember("section_exists_{$sectionId}", self::CACHE_DURATION, function () use ($sectionId) {
            return DB::connection('shelter')
                ->table('section')
                ->where('id', $sectionId)
                ->exists();
        });
    }

    /**
     * Validate if a category exists in Atiqah's database
     */
    public static function validateCategory($categoryId): bool
    {
        if (empty($categoryId)) {
            return false;
        }

        return Cache::remember("category_exists_{$categoryId}", self::CACHE_DURATION, function () use ($categoryId) {
            return DB::connection('shelter')
                ->table('category')
                ->where('id', $categoryId)
                ->exists();
        });
    }

    /**
     * Validate if an inventory item exists in Atiqah's database
     */
    public static function validateInventory($inventoryId): bool
    {
        if (empty($inventoryId)) {
            return false;
        }

        return Cache::remember("inventory_exists_{$inventoryId}", self::CACHE_DURATION, function () use ($inventoryId) {
            return DB::connection('shelter')
                ->table('inventory')
                ->where('id', $inventoryId)
                ->exists();
        });
    }

    /**
     * Check if slot has available capacity
     */
    public static function slotHasCapacity($slotId): bool
    {
        if (empty($slotId)) {
            return false;
        }

        // Get slot capacity
        $slot = DB::connection('shelter')
            ->table('slot')
            ->where('id', $slotId)
            ->first(['capacity']);

        if (!$slot) {
            return false;
        }

        // Count animals in this slot (cross-database query to Shafiqah)
        $animalCount = DB::connection('animals')
            ->table('animal')
            ->where('slotID', $slotId)
            ->count();

        return $animalCount < $slot->capacity;
    }

    /**
     * ========================================
     * SHAFIQAH'S DATABASE (Animal & Medical)
     * ========================================
     */

    /**
     * Validate if an animal exists in Shafiqah's database
     */
    public static function validateAnimal($animalId): bool
    {
        if (empty($animalId)) {
            return false;
        }

        return Cache::remember("animal_exists_{$animalId}", self::CACHE_DURATION, function () use ($animalId) {
            return DB::connection('animals')
                ->table('animal')
                ->where('id', $animalId)
                ->exists();
        });
    }

    /**
     * Validate if an animal profile exists in Shafiqah's database
     */
    public static function validateAnimalProfile($animalProfileId): bool
    {
        if (empty($animalProfileId)) {
            return false;
        }

        return Cache::remember("animal_profile_exists_{$animalProfileId}", self::CACHE_DURATION, function () use ($animalProfileId) {
            return DB::connection('animals')
                ->table('animal_profile')
                ->where('id', $animalProfileId)
                ->exists();
        });
    }

    /**
     * Validate if a clinic exists in Shafiqah's database
     */
    public static function validateClinic($clinicId): bool
    {
        if (empty($clinicId)) {
            return false;
        }

        return Cache::remember("clinic_exists_{$clinicId}", self::CACHE_DURATION, function () use ($clinicId) {
            return DB::connection('animals')
                ->table('clinic')
                ->where('id', $clinicId)
                ->exists();
        });
    }

    /**
     * Validate if a vet exists in Shafiqah's database
     */
    public static function validateVet($vetId): bool
    {
        if (empty($vetId)) {
            return false;
        }

        return Cache::remember("vet_exists_{$vetId}", self::CACHE_DURATION, function () use ($vetId) {
            return DB::connection('animals')
                ->table('vet')
                ->where('id', $vetId)
                ->exists();
        });
    }

    /**
     * Validate if a medical record exists in Shafiqah's database
     */
    public static function validateMedical($medicalId): bool
    {
        if (empty($medicalId)) {
            return false;
        }

        return Cache::remember("medical_exists_{$medicalId}", self::CACHE_DURATION, function () use ($medicalId) {
            return DB::connection('animals')
                ->table('medical')
                ->where('id', $medicalId)
                ->exists();
        });
    }

    /**
     * Validate if a vaccination record exists in Shafiqah's database
     */
    public static function validateVaccination($vaccinationId): bool
    {
        if (empty($vaccinationId)) {
            return false;
        }

        return Cache::remember("vaccination_exists_{$vaccinationId}", self::CACHE_DURATION, function () use ($vaccinationId) {
            return DB::connection('animals')
                ->table('vaccination')
                ->where('id', $vaccinationId)
                ->exists();
        });
    }

    /**
     * Check if animal is available for adoption
     */
    public static function animalAvailableForAdoption($animalId): bool
    {
        if (empty($animalId)) {
            return false;
        }

        $animal = DB::connection('animals')
            ->table('animal')
            ->where('id', $animalId)
            ->first(['adoption_status']);

        return $animal && $animal->adoption_status === 'Not Adopted';
    }

    /**
     * ========================================
     * DANISH'S DATABASE (Booking & Adoption)
     * ========================================
     */

    /**
     * Validate if a booking exists in Danish's database
     */
    public static function validateBooking($bookingId): bool
    {
        if (empty($bookingId)) {
            return false;
        }

        return Cache::remember("booking_exists_{$bookingId}", self::CACHE_DURATION, function () use ($bookingId) {
            return DB::connection('booking')
                ->table('booking')
                ->where('id', $bookingId)
                ->exists();
        });
    }

    /**
     * Validate if a transaction exists in Danish's database
     */
    public static function validateTransaction($transactionId): bool
    {
        if (empty($transactionId)) {
            return false;
        }

        return Cache::remember("transaction_exists_{$transactionId}", self::CACHE_DURATION, function () use ($transactionId) {
            return DB::connection('booking')
                ->table('transaction')
                ->where('id', $transactionId)
                ->exists();
        });
    }

    /**
     * Validate if an adoption record exists in Danish's database
     */
    public static function validateAdoption($adoptionId): bool
    {
        if (empty($adoptionId)) {
            return false;
        }

        return Cache::remember("adoption_exists_{$adoptionId}", self::CACHE_DURATION, function () use ($adoptionId) {
            return DB::connection('booking')
                ->table('adoption')
                ->where('id', $adoptionId)
                ->exists();
        });
    }

    /**
     * Validate if a visit list exists in Danish's database
     */
    public static function validateVisitList($visitListId): bool
    {
        if (empty($visitListId)) {
            return false;
        }

        return Cache::remember("visit_list_exists_{$visitListId}", self::CACHE_DURATION, function () use ($visitListId) {
            return DB::connection('booking')
                ->table('visit_list')
                ->where('id', $visitListId)
                ->exists();
        });
    }

    /**
     * Check if user already has a visit list
     */
    public static function userHasVisitList($userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return DB::connection('booking')
            ->table('visit_list')
            ->where('userID', $userId)
            ->exists();
    }

    /**
     * Check if animal is already in user's visit list
     */
    public static function animalInVisitList($userId, $animalId): bool
    {
        if (empty($userId) || empty($animalId)) {
            return false;
        }

        $visitList = DB::connection('booking')
            ->table('visit_list')
            ->where('userID', $userId)
            ->first(['id']);

        if (!$visitList) {
            return false;
        }

        return DB::connection('booking')
            ->table('visit_list_animal')
            ->where('listID', $visitList->id)
            ->where('animalID', $animalId)
            ->exists();
    }

    /**
     * ========================================
     * BATCH VALIDATION
     * ========================================
     */

    /**
     * Validate multiple user IDs at once
     */
    public static function validateUsers(array $userIds): array
    {
        $userIds = array_filter($userIds); // Remove empty values

        if (empty($userIds)) {
            return [];
        }

        $existingIds = DB::connection('users')
            ->table('users')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->toArray();

        return [
            'valid' => $existingIds,
            'invalid' => array_diff($userIds, $existingIds),
        ];
    }

    /**
     * Validate multiple animal IDs at once
     */
    public static function validateAnimals(array $animalIds): array
    {
        $animalIds = array_filter($animalIds); // Remove empty values

        if (empty($animalIds)) {
            return [];
        }

        $existingIds = DB::connection('animals')
            ->table('animal')
            ->whereIn('id', $animalIds)
            ->pluck('id')
            ->toArray();

        return [
            'valid' => $existingIds,
            'invalid' => array_diff($animalIds, $existingIds),
        ];
    }

    /**
     * ========================================
     * CACHE MANAGEMENT
     * ========================================
     */

    /**
     * Clear all validation caches
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Clear cache for specific user
     */
    public static function clearUserCache($userId): void
    {
        Cache::forget("user_exists_{$userId}");
    }

    /**
     * Clear cache for specific animal
     */
    public static function clearAnimalCache($animalId): void
    {
        Cache::forget("animal_exists_{$animalId}");
    }

    /**
     * Clear cache for specific role
     */
    public static function clearRoleCache($roleId): void
    {
        Cache::forget("role_exists_{$roleId}");
    }

    /**
     * Clear cache for specific image
     */
    public static function clearImageCache($imageId): void
    {
        Cache::forget("image_exists_{$imageId}");
    }

    /**
     * Clear cache for specific medical record
     */
    public static function clearMedicalCache($medicalId): void
    {
        Cache::forget("medical_exists_{$medicalId}");
    }

    /**
     * Clear cache for specific vaccination
     */
    public static function clearVaccinationCache($vaccinationId): void
    {
        Cache::forget("vaccination_exists_{$vaccinationId}");
    }

    /**
     * Clear cache for specific inventory
     */
    public static function clearInventoryCache($inventoryId): void
    {
        Cache::forget("inventory_exists_{$inventoryId}");
    }

    /**
     * Clear cache for specific adoption
     */
    public static function clearAdoptionCache($adoptionId): void
    {
        Cache::forget("adoption_exists_{$adoptionId}");
    }

    /**
     * Clear cache for specific visit list
     */
    public static function clearVisitListCache($visitListId): void
    {
        Cache::forget("visit_list_exists_{$visitListId}");
    }
}
