<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Reports and Images are in Eilya's database
     * Users are in Taufiq's database (cross-database reference)
     */
    public function run()
    {
        $this->command->info('Starting Report Seeder...');
        $this->command->info('========================================');

        // Load CSV
        $csvPath = database_path('seeders/report.csv');
        if (!file_exists($csvPath)) {
            $this->command->error('CSV file not found at: ' . $csvPath);
            return;
        }

        $rows = array_map('str_getcsv', file($csvPath));
        $header = array_shift($rows); // first row as header

        $data = [];
        foreach ($rows as $row) {
            $data[] = array_combine($header, $row);
        }

        // Get public users (exclude admin & caretaker) from Taufiq's database
        $this->command->info('Fetching eligible users from Taufiq\'s database...');

        $excludedRoles = ['admin', 'caretaker'];
        $userIDs = User::whereDoesntHave('roles', function ($q) use ($excludedRoles) {
            $q->whereIn('name', $excludedRoles);
        })->pluck('id')->toArray();

        if (empty($userIDs)) {
            $this->command->error('No eligible users found!');
            return;
        }

        $this->command->info("Found " . count($userIDs) . " eligible users");

        $reportStatuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];

        // Priority-based descriptions from stray-reporting/create.blade.php
        $descriptions = [
            // URGENT - Critical Priority
            'Injured animal - Critical condition',
            'Trapped animal - Immediate rescue needed',
            'Aggressive animal - Public safety risk',

            // HIGH PRIORITY
            'Sick animal - Needs medical attention',
            'Mother with puppies/kittens - Family rescue',
            'Young animal (puppy/kitten) - Vulnerable',
            'Malnourished animal - Needs care',

            // STANDARD - Normal Priority
            'Healthy stray - Needs rescue',
            'Abandoned pet - Recent',
            'Friendly stray - Approachable',
        ];

        // Define available images by category
        $imageCategories = [
            'cat' => [],
            'dog' => [],
            'dogcat' => []
        ];

        // Cat images: cat1.jpg to cat3.jpg
        for ($i = 1; $i <= 3; $i++) {
            $imageCategories['cat'][] = "reports/cat{$i}.jpg";
        }

        // Dog images: dog1.jpg to dog6.jpg
        for ($i = 1; $i <= 6; $i++) {
            $imageCategories['dog'][] = "reports/dog{$i}.jpg";
        }

        // Dogcat images: dogcat1.jpg to dogcat3.jpg
        for ($i = 1; $i <= 3; $i++) {
            $imageCategories['dogcat'][] = "reports/dogcat{$i}.jpg";
        }

        $reports = [];

        // Maximum coordinate offset (~1 km)
        $maxOffset = 0.01;

        $this->command->info('Preparing reports...');

        for ($i = 0; $i < 200; $i++) {
            $row = $data[array_rand($data)]; // pick random CSV row

            // Random date in last 2 years
            $createdAt = Carbon::now()->subDays(rand(0, 730));

            // Randomize coordinates slightly
            $latitude  = $row['latitude']  + (rand(-1000, 1000) / 100000); // ±0.01
            $longitude = $row['longitude'] + (rand(-1000, 1000) / 100000); // ±0.01

            $reports[] = [
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'address'       => $row['address'],
                'city'          => $row['city'],
                'state'         => $row['state'],
                'report_status' => in_array($row['report_status'], $reportStatuses) ? $row['report_status'] : $reportStatuses[array_rand($reportStatuses)],
                'description'   => $descriptions[array_rand($descriptions)], // Use priority-based descriptions

                // Cross-database reference to Taufiq's users table
                'userID'        => isset($row['userID']) && in_array($row['userID'], $userIDs) ? $row['userID'] : $userIDs[array_rand($userIDs)],

                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }

        // Use transaction for Eilya's database
        DB::connection('eilya')->beginTransaction();

        try {
            $this->command->info('Inserting reports into Eilya\'s database...');

            // Insert reports in chunks to avoid SQL Server 2100 parameter limit
            // Each report has 10 columns, so chunk size of 100 = 1000 parameters (safe)
            $chunkSize = 100;
            $totalInserted = 0;

            foreach (array_chunk($reports, $chunkSize) as $chunk) {
                DB::connection('eilya')->table('report')->insert($chunk);
                $totalInserted += count($chunk);
                $this->command->info("  Inserted {$totalInserted} / " . count($reports) . " reports...");
            }

            // Get the IDs of the inserted reports from Eilya's database
            $insertedReportIDs = DB::connection('eilya')
                ->table('report')
                ->orderBy('id', 'desc')
                ->limit(600)
                ->pluck('id')
                ->toArray();

            $this->command->info('');
            $this->command->info('Assigning images to reports in Eilya\'s database...');

            // Assign images to reports (both in Eilya's database)
            $this->assignImagesToReports($insertedReportIDs, $imageCategories);

            DB::connection('eilya')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Report Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total reports created: " . count($reports));
            $this->command->info("Database: Eilya (MySQL)");
            $this->command->info("User references: Taufiq (PostgreSQL)");
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding reports: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }

    /**
     * Assign images to reports - each report gets images from same category only
     * Both reports and images are in Eilya's database
     */
    private function assignImagesToReports($reportIDs, $imageCategories)
    {
        $images = [];
        $totalImages = 0;
        $categoryStats = ['cat' => 0, 'dog' => 0, 'dogcat' => 0];

        foreach ($reportIDs as $reportID) {
            // Randomly select ONE category for this report
            $categoryKeys = array_keys($imageCategories);
            $selectedCategory = $categoryKeys[array_rand($categoryKeys)];

            // Get images from the selected category
            $availableImages = $imageCategories[$selectedCategory];

            // Randomly assign 1-3 images from this category only
            $numImages = rand(1, min(3, count($availableImages)));

            // Randomly select images for this report from the same category
            $selectedImages = array_rand(array_flip($availableImages), $numImages);

            // Handle case where only 1 image is selected (array_rand returns string, not array)
            if (!is_array($selectedImages)) {
                $selectedImages = [$selectedImages];
            }

            foreach ($selectedImages as $imagePath) {
                $images[] = [
                    'image_path' => $imagePath,
                    'animalID'   => null, // Cross-database reference to Shafiqah (null for now)
                    'reportID'   => $reportID, // Same database reference to Eilya
                    'clinicID'   => null, // Cross-database reference to Shafiqah (null for now)
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalImages++;
            }

            // Track category usage
            $categoryStats[$selectedCategory]++;
        }

        // Insert images in chunks to Eilya's database
        // Each image has 6 columns, so chunk size of 300 = 1800 parameters (safe)
        $chunkSize = 300;
        $totalInsertedImages = 0;

        foreach (array_chunk($images, $chunkSize) as $chunk) {
            DB::connection('eilya')->table('image')->insert($chunk);
            $totalInsertedImages += count($chunk);
            $this->command->info("  Inserted {$totalInsertedImages} / {$totalImages} images...");
        }

        $this->command->info('');
        $this->command->info("Total images assigned to reports: {$totalImages}");
        $avgImages = round($totalImages / count($reportIDs), 1);
        $this->command->info("Average images per report: {$avgImages}");
        $this->command->info('');
        $this->command->info('Reports by animal category:');
        $this->command->info("  - Cat reports: {$categoryStats['cat']}");
        $this->command->info("  - Dog reports: {$categoryStats['dog']}");
        $this->command->info("  - Dog & Cat reports: {$categoryStats['dogcat']}");
    }
}
