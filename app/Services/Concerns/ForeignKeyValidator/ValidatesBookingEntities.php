<?php

namespace App\Services\Concerns\ForeignKeyValidator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ValidatesBookingEntities
{
    public static function validateBooking($bookingId): bool
    {
        if (empty($bookingId)) {
            return false;
        }

        return Cache::remember("booking_exists_{$bookingId}", self::CACHE_DURATION, function () use ($bookingId) {
            return DB::connection('booking')->table('booking')->where('id', $bookingId)->exists();
        });
    }

    public static function validateTransaction($transactionId): bool
    {
        if (empty($transactionId)) {
            return false;
        }

        return Cache::remember("transaction_exists_{$transactionId}", self::CACHE_DURATION, function () use ($transactionId) {
            return DB::connection('booking')->table('transaction')->where('id', $transactionId)->exists();
        });
    }

    public static function validateAdoption($adoptionId): bool
    {
        if (empty($adoptionId)) {
            return false;
        }

        return Cache::remember("adoption_exists_{$adoptionId}", self::CACHE_DURATION, function () use ($adoptionId) {
            return DB::connection('booking')->table('adoption')->where('id', $adoptionId)->exists();
        });
    }

    public static function validateVisitList($visitListId): bool
    {
        if (empty($visitListId)) {
            return false;
        }

        return Cache::remember("visit_list_exists_{$visitListId}", self::CACHE_DURATION, function () use ($visitListId) {
            return DB::connection('booking')->table('visit_list')->where('id', $visitListId)->exists();
        });
    }

    public static function userHasVisitList($userId): bool
    {
        if (empty($userId)) {
            return false;
        }

        return DB::connection('booking')->table('visit_list')->where('userID', $userId)->exists();
    }

    public static function animalInVisitList($userId, $animalId): bool
    {
        if (empty($userId) || empty($animalId)) {
            return false;
        }

        $visitList = DB::connection('booking')->table('visit_list')->where('userID', $userId)->first(['id']);

        if (!$visitList) {
            return false;
        }

        return DB::connection('booking')
            ->table('visit_list_animal')
            ->where('listID', $visitList->id)
            ->where('animalID', $animalId)
            ->exists();
    }
}
