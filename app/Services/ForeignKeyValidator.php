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
            return DB::connection('taufiq')
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
            return DB::connection('taufiq')
                ->table('adopter_profile')
                ->where('id', $adopterProfileId)
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
            return DB::connection('eilya')
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
            return DB::connection('eilya')
                ->table('rescue')
                ->where('id', $rescueId)
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
            return DB::connection('atiqah')
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
            return DB::connection('atiqah')
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
            return DB::connection('atiqah')
                ->table('category')
                ->where('id', $categoryId)
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
        $slot = DB::connection('atiqah')
            ->table('slot')
            ->where('id', $slotId)
            ->first(['capacity']);

        if (!$slot) {
            return false;
        }

        // Count animals in this slot (cross-database query to Shafiqah)
        $animalCount = DB::connection('shafiqah')
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
            return DB::connection('shafiqah')
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
            return DB::connection('shafiqah')
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
            return DB::connection('shafiqah')
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
            return DB::connection('shafiqah')
                ->table('vet')
                ->where('id', $vetId)
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

        $animal = DB::connection('shafiqah')
            ->table('animal')
            ->where('id', $animalId)
            ->first(['adoption_status']);

        return $animal && $animal->adoption_status === 'available';
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
            return DB::connection('danish')
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
            return DB::connection('danish')
                ->table('transaction')
                ->where('id', $transactionId)
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

        return DB::connection('danish')
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

        $visitList = DB::connection('danish')
            ->table('visit_list')
            ->where('userID', $userId)
            ->first(['id']);

        if (!$visitList) {
            return false;
        }

        return DB::connection('danish')
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

        $existingIds = DB::connection('taufiq')
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

        $existingIds = DB::connection('shafiqah')
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
}
