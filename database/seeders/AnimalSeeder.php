<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\Shared\UploadsCloudinaryImages;
use Database\Seeders\Concerns\AnimalSeeder\SeedsAnimals;
use Database\Seeders\Concerns\AnimalSeeder\BuildsRescueGroups;
use Database\Seeders\Concerns\AnimalSeeder\UpdatesSlotStatus;
use Database\Seeders\Concerns\AnimalSeeder\SeedsMedicalRecords;
use Database\Seeders\Concerns\AnimalSeeder\SeedsVaccinationRecords;
use Database\Seeders\Concerns\AnimalSeeder\AssignsAnimalImages;

class AnimalSeeder extends Seeder
{
    use UploadsCloudinaryImages,
        BuildsRescueGroups,
        UpdatesSlotStatus,
        SeedsMedicalRecords,
        SeedsVaccinationRecords,
        AssignsAnimalImages,
        SeedsAnimals;
}
