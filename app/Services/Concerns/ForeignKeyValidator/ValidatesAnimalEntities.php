<?php

namespace App\Services\Concerns\ForeignKeyValidator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ValidatesAnimalEntities
{
    public static function validateAnimal($animalId): bool
    {
        if (empty($animalId)) {
            return false;
        }

        return Cache::remember("animal_exists_{$animalId}", self::CACHE_DURATION, function () use ($animalId) {
            return DB::connection('animals')->table('animal')->where('id', $animalId)->exists();
        });
    }

    public static function validateAnimalProfile($animalProfileId): bool
    {
        if (empty($animalProfileId)) {
            return false;
        }

        return Cache::remember("animal_profile_exists_{$animalProfileId}", self::CACHE_DURATION, function () use ($animalProfileId) {
            return DB::connection('animals')->table('animal_profile')->where('id', $animalProfileId)->exists();
        });
    }

    public static function validateClinic($clinicId): bool
    {
        if (empty($clinicId)) {
            return false;
        }

        return Cache::remember("clinic_exists_{$clinicId}", self::CACHE_DURATION, function () use ($clinicId) {
            return DB::connection('animals')->table('clinic')->where('id', $clinicId)->exists();
        });
    }

    public static function validateVet($vetId): bool
    {
        if (empty($vetId)) {
            return false;
        }

        return Cache::remember("vet_exists_{$vetId}", self::CACHE_DURATION, function () use ($vetId) {
            return DB::connection('animals')->table('vet')->where('id', $vetId)->exists();
        });
    }

    public static function validateMedical($medicalId): bool
    {
        if (empty($medicalId)) {
            return false;
        }

        return Cache::remember("medical_exists_{$medicalId}", self::CACHE_DURATION, function () use ($medicalId) {
            return DB::connection('animals')->table('medical')->where('id', $medicalId)->exists();
        });
    }

    public static function validateVaccination($vaccinationId): bool
    {
        if (empty($vaccinationId)) {
            return false;
        }

        return Cache::remember("vaccination_exists_{$vaccinationId}", self::CACHE_DURATION, function () use ($vaccinationId) {
            return DB::connection('animals')->table('vaccination')->where('id', $vaccinationId)->exists();
        });
    }

    public static function animalAvailableForAdoption($animalId): bool
    {
        if (empty($animalId)) {
            return false;
        }

        $animal = DB::connection('animals')->table('animal')->where('id', $animalId)->first(['adoption_status']);

        return $animal && $animal->adoption_status === 'Not Adopted';
    }
}
