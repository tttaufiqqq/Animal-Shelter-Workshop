<?php

namespace Database\Seeders\Concerns\ReportSeeder;

use Illuminate\Support\Facades\DB;

trait AssignsReportImages
{
    private function assignImagesToReports(array $reportIDs, array $imageCategories): void
    {
        $images        = [];
        $totalImages   = 0;
        $categoryStats = ['cat' => 0, 'dog' => 0, 'dogcat' => 0];

        foreach ($reportIDs as $reportID) {
            $categoryKeys     = array_keys($imageCategories);
            $selectedCategory = $categoryKeys[array_rand($categoryKeys)];
            $availableImages  = $imageCategories[$selectedCategory];

            $numImages      = rand(1, min(3, count($availableImages)));
            $selectedImages = array_rand(array_flip($availableImages), $numImages);

            if (!is_array($selectedImages)) {
                $selectedImages = [$selectedImages];
            }

            foreach ($selectedImages as $imagePath) {
                $cloudinaryPath = $this->uploadToCloudinary($imagePath);

                if ($cloudinaryPath === null) {
                    $this->command->warn("  Skipping image for report {$reportID}: {$imagePath}");
                    continue;
                }

                $images[] = [
                    'image_path' => $cloudinaryPath,
                    'animalID'   => null,
                    'reportID'   => $reportID,
                    'clinicID'   => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalImages++;
            }

            $categoryStats[$selectedCategory]++;
        }

        $chunkSize           = 300;
        $totalInsertedImages = 0;
        foreach (array_chunk($images, $chunkSize) as $chunk) {
            DB::connection('reporting')->table('image')->insert($chunk);
            $totalInsertedImages += count($chunk);
            $this->command->info("  Inserted {$totalInsertedImages} / {$totalImages} images...");
        }

        $avg = count($reportIDs) > 0 ? round($totalImages / count($reportIDs), 1) : 0;
        $this->command->info('');
        $this->command->info("Total images assigned to reports: {$totalImages}");
        $this->command->info("Average images per report: {$avg}");
        $this->command->info('');
        $this->command->info('Reports by animal category:');
        $this->command->info("  - Cat reports: {$categoryStats['cat']}");
        $this->command->info("  - Dog reports: {$categoryStats['dog']}");
        $this->command->info("  - Dog & Cat reports: {$categoryStats['dogcat']}");
    }
}
