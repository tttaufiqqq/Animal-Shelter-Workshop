<?php

namespace Database\Seeders\Concerns\AnimalSeeder;

use Illuminate\Support\Facades\DB;

trait AssignsAnimalImages
{
    private function assignImagesToAnimals(array $animals, array $catImages, array $dogImages): void
    {
        $images      = [];
        $totalImages = 0;

        foreach ($animals as $animal) {
            $numImages       = rand(1, 3);
            $availableImages = $animal->species === 'Cat' ? $catImages : $dogImages;
            $selectedImages  = array_rand(array_flip($availableImages), min($numImages, count($availableImages)));

            if (!is_array($selectedImages)) {
                $selectedImages = [$selectedImages];
            }

            foreach ($selectedImages as $imagePath) {
                $cloudinaryPath = $this->uploadToCloudinary($imagePath);

                if ($cloudinaryPath === null) {
                    $this->command->warn("  Skipping image for animal {$animal->id}: {$imagePath}");
                    continue;
                }

                $images[] = [
                    'image_path' => $cloudinaryPath,
                    'animalID'   => $animal->id,
                    'reportID'   => null,
                    'clinicID'   => null,
                    'created_at' => $animal->created_at,
                    'updated_at' => $animal->created_at,
                ];
                $totalImages++;
            }
        }

        $this->command->info("Inserting animal images into Eilya's database...");
        foreach (array_chunk($images, 300) as $chunk) {
            DB::connection('reporting')->table('image')->insert($chunk);
        }

        $avgImages = count($animals) > 0 ? round($totalImages / count($animals), 1) : 0;
        $this->command->info("Total images assigned to animals: {$totalImages}");
        $this->command->info("Average images per animal: {$avgImages}");
    }
}
