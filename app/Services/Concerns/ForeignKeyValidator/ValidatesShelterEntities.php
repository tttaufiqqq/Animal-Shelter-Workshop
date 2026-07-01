<?php

namespace App\Services\Concerns\ForeignKeyValidator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ValidatesShelterEntities
{
    public static function validateSlot($slotId): bool
    {
        if (empty($slotId)) {
            return false;
        }

        return Cache::remember("slot_exists_{$slotId}", self::CACHE_DURATION, function () use ($slotId) {
            return DB::connection('shelter')->table('slot')->where('id', $slotId)->exists();
        });
    }

    public static function validateSection($sectionId): bool
    {
        if (empty($sectionId)) {
            return false;
        }

        return Cache::remember("section_exists_{$sectionId}", self::CACHE_DURATION, function () use ($sectionId) {
            return DB::connection('shelter')->table('section')->where('id', $sectionId)->exists();
        });
    }

    public static function validateCategory($categoryId): bool
    {
        if (empty($categoryId)) {
            return false;
        }

        return Cache::remember("category_exists_{$categoryId}", self::CACHE_DURATION, function () use ($categoryId) {
            return DB::connection('shelter')->table('category')->where('id', $categoryId)->exists();
        });
    }

    public static function validateInventory($inventoryId): bool
    {
        if (empty($inventoryId)) {
            return false;
        }

        return Cache::remember("inventory_exists_{$inventoryId}", self::CACHE_DURATION, function () use ($inventoryId) {
            return DB::connection('shelter')->table('inventory')->where('id', $inventoryId)->exists();
        });
    }

    public static function slotHasCapacity($slotId): bool
    {
        if (empty($slotId)) {
            return false;
        }

        $slot = DB::connection('shelter')->table('slot')->where('id', $slotId)->first(['capacity']);

        if (!$slot) {
            return false;
        }

        $animalCount = DB::connection('animals')->table('animal')->where('slotID', $slotId)->count();

        return $animalCount < $slot->capacity;
    }
}
