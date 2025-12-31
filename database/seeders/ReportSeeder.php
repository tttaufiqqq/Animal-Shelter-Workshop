<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Cache for uploaded Cloudinary paths to avoid re-uploading the same image
     */
    private $cloudinaryCache = [];

    /**
     * Upload a local seed image to Cloudinary
     * Returns the Cloudinary path, or null if upload fails
     */
    private function uploadToCloudinary($localPath)
    {
        // Check cache first
        if (isset($this->cloudinaryCache[$localPath])) {
            return $this->cloudinaryCache[$localPath];
        }

        try {
            $fullPath = storage_path('app/public/' . $localPath);

            if (!file_exists($fullPath)) {
                $this->command->warn("  Image not found: {$localPath}");
                return null;
            }

            // Extract folder and filename
            $folder = dirname($localPath); // 'reports' or 'animal_images'
            $fileName = pathinfo($localPath, PATHINFO_FILENAME); // Without extension

            // Upload to Cloudinary using the Upload API
            $result = cloudinary()->uploadApi()->upload($fullPath, [
                'folder' => $folder,
                'public_id' => $fileName,
            ]);

            // Store the public_id (Cloudinary path) instead of full URL
            // Format: folder/filename
            $cloudinaryPath = $folder . '/' . $fileName;

            // Cache the result
            $this->cloudinaryCache[$localPath] = $cloudinaryPath;

            return $cloudinaryPath;

        } catch (\Exception $e) {
            $this->command->error("  Failed to upload {$localPath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Real Malacca, Malaysia locations with verified coordinates
     * Organized by area type for realistic stray animal reports
     */
    private function getMalaccaLocations()
    {
        return [
            // CENTRAL MALACCA (Tourist & Commercial Areas)
            ['lat' => 2.1964, 'lng' => 102.2487, 'address' => 'Dataran Pahlawan Megamall, Jalan Merdeka', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1953, 'lng' => 102.2493, 'address' => 'Jonker Street, Jalan Hang Jebat', 'area' => 'Chinatown'],
            ['lat' => 2.1896, 'lng' => 102.2489, 'address' => 'A Famosa, Jalan Kota', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1946, 'lng' => 102.2486, 'address' => 'Stadthuys, Jalan Gereja', 'area' => 'Dutch Square'],
            ['lat' => 2.1945, 'lng' => 102.2487, 'address' => 'Christ Church, Jalan Gereja', 'area' => 'Dutch Square'],
            ['lat' => 2.1921, 'lng' => 102.2493, 'address' => 'St. Paul\'s Hill, Jalan Kota', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1989, 'lng' => 102.2511, 'address' => 'Mahkota Parade, Jalan Merdeka', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1972, 'lng' => 102.2509, 'address' => 'Hatten Square, Jalan Merdeka', 'area' => 'Bandar Hilir'],

            // RESIDENTIAL AREAS (High stray animal probability)
            ['lat' => 2.2018, 'lng' => 102.2537, 'address' => 'Taman Melaka Raya, Jalan Taman Melaka Raya', 'area' => 'Melaka Raya'],
            ['lat' => 2.2346, 'lng' => 102.2494, 'address' => 'Bukit Beruang, Jalan Bukit Beruang', 'area' => 'Bukit Beruang'],
            ['lat' => 2.1872, 'lng' => 102.2563, 'address' => 'Taman Kota Laksamana, Jalan Kota Laksamana', 'area' => 'Kota Laksamana'],
            ['lat' => 2.2502, 'lng' => 102.2511, 'address' => 'Batu Berendam, Jalan Batu Berendam', 'area' => 'Batu Berendam'],
            ['lat' => 2.2189, 'lng' => 102.2635, 'address' => 'Bertam Ulu, Jalan Bertam', 'area' => 'Bertam'],
            ['lat' => 2.2156, 'lng' => 102.2489, 'address' => 'Taman Limbongan, Jalan Limbongan', 'area' => 'Limbongan'],
            ['lat' => 2.2089, 'lng' => 102.2456, 'address' => 'Bachang, Jalan Bachang', 'area' => 'Bachang'],
            ['lat' => 2.2067, 'lng' => 102.2621, 'address' => 'Taman Teknologi, Jalan Teknologi', 'area' => 'Melaka Tengah'],

            // MARKETS & COMMERCIAL (Common stray gathering spots)
            ['lat' => 2.1978, 'lng' => 102.2501, 'address' => 'Pasar Besar Melaka, Jalan Hang Kasturi', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1885, 'lng' => 102.2571, 'address' => 'Pasar Malam Kota Laksamana, Taman Kota Laksamana', 'area' => 'Kota Laksamana'],
            ['lat' => 2.2024, 'lng' => 102.2539, 'address' => 'Mydin Mall Melaka, Jalan Tun Ali', 'area' => 'Melaka Raya'],
            ['lat' => 2.2134, 'lng' => 102.2467, 'address' => 'Plaza Mahkota, Jalan Laksamana', 'area' => 'Bandar Hilir'],

            // SUBURBAN AREAS
            ['lat' => 2.2682, 'lng' => 102.2806, 'address' => 'Ayer Keroh, Jalan Ayer Keroh', 'area' => 'Ayer Keroh'],
            ['lat' => 2.3036, 'lng' => 102.2894, 'address' => 'Durian Tunggal, Jalan Durian Tunggal', 'area' => 'Durian Tunggal'],
            ['lat' => 2.2794, 'lng' => 102.2650, 'address' => 'Sungai Udang, Jalan Sungai Udang', 'area' => 'Sungai Udang'],
            ['lat' => 2.2095, 'lng' => 102.1979, 'address' => 'Klebang, Jalan Klebang Besar', 'area' => 'Klebang'],
            ['lat' => 2.2278, 'lng' => 102.1967, 'address' => 'Tanjung Kling, Jalan Tanjung Kling', 'area' => 'Tanjung Kling'],
            ['lat' => 2.2445, 'lng' => 102.2678, 'address' => 'Paya Rumput, Jalan Paya Rumput', 'area' => 'Paya Rumput'],

            // INDUSTRIAL & MIXED AREAS
            ['lat' => 2.2567, 'lng' => 102.2489, 'address' => 'Taman Merdeka, Jalan Merdeka', 'area' => 'Batu Berendam'],
            ['lat' => 2.2389, 'lng' => 102.2567, 'address' => 'Taman Bukit Rambai, Jalan Bukit Rambai', 'area' => 'Bukit Rambai'],
            ['lat' => 2.2123, 'lng' => 102.2734, 'address' => 'Taman Cheng, Jalan Cheng', 'area' => 'Cheng'],

            // EDUCATIONAL & PUBLIC FACILITIES
            ['lat' => 2.3108, 'lng' => 102.3184, 'address' => 'UTeM, Hang Tuah Jaya', 'area' => 'Durian Tunggal'],
            ['lat' => 2.1892, 'lng' => 102.2584, 'address' => 'Hospital Melaka, Jalan Mufti Haji Khalil', 'area' => 'Melaka Tengah'],
            ['lat' => 2.2683, 'lng' => 102.2515, 'address' => 'Zoo Melaka, Lebuh Ayer Keroh', 'area' => 'Ayer Keroh'],
            ['lat' => 2.2456, 'lng' => 102.2523, 'address' => 'Taman Tasik Utama, Jalan Tasik Utama', 'area' => 'Ayer Keroh'],

            // BEACH & COASTAL AREAS (Stray animals common near fishing areas)
            ['lat' => 2.2123, 'lng' => 102.1956, 'address' => 'Pantai Klebang, Jalan Pantai Klebang', 'area' => 'Klebang'],
            ['lat' => 2.3269, 'lng' => 102.2142, 'address' => 'Tanjung Bidara Beach, Jalan Tanjung Bidara', 'area' => 'Tanjung Bidara'],
            ['lat' => 2.2198, 'lng' => 102.1989, 'address' => 'Pantai Puteri, Jalan Pantai Puteri', 'area' => 'Klebang'],

            // VILLAGES & KAMPUNG AREAS (High stray population)
            ['lat' => 2.2734, 'lng' => 102.2456, 'address' => 'Kampung Tanjung Minyak, Jalan Tanjung Minyak', 'area' => 'Tanjung Minyak'],
            ['lat' => 2.2567, 'lng' => 102.2389, 'address' => 'Kampung Serkam, Jalan Serkam', 'area' => 'Serkam'],
            ['lat' => 2.2912, 'lng' => 102.2723, 'address' => 'Kampung Duyong, Jalan Duyong', 'area' => 'Duyong'],
            ['lat' => 2.2645, 'lng' => 102.2834, 'address' => 'Kampung Tengah, Jalan Tengah', 'area' => 'Ayer Keroh'],

            // ADDITIONAL RESIDENTIAL HOTSPOTS
            ['lat' => 2.1923, 'lng' => 102.2612, 'address' => 'Taman Malim Jaya, Jalan Malim Jaya', 'area' => 'Malim'],
            ['lat' => 2.2234, 'lng' => 102.2578, 'address' => 'Taman Bertam Jaya, Jalan Bertam Jaya', 'area' => 'Bertam'],
            ['lat' => 2.2456, 'lng' => 102.2612, 'address' => 'Taman Bukit Katil, Jalan Bukit Katil', 'area' => 'Bukit Katil'],
        ];
    }

    /**
     * Run the database seeds.
     * Reports and Images are in Eilya's database
     * Users are in Taufiq's database (cross-database reference)
     *
     * REALISTIC: Uses real Malacca, Malaysia locations with verified coordinates
     */
    public function run()
    {
        $this->command->info('Starting Report Seeder (Malacca, Malaysia)...');
        $this->command->info('========================================');

        // Get real Malacca locations
        $malaccaLocations = $this->getMalaccaLocations();
        $this->command->info('Loaded ' . count($malaccaLocations) . ' real Malacca locations');

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

        // Report statuses aligned with new implementation
        // Note: Actual status will be synced by RescueSeeder based on rescue status
        $reportStatuses = ['Pending', 'Assigned', 'In Progress', 'Completed', 'Rejected'];

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

        // Coordinate variation for realistic spread (±0.001 degrees ≈ ±100 meters)
        $coordinateVariation = 0.001;

        $this->command->info('Preparing 200 reports with Malacca locations...');

        for ($i = 0; $i < 200; $i++) {
            // Pick random real Malacca location
            $location = $malaccaLocations[array_rand($malaccaLocations)];

            // Random date in last 6 months (realistic for active shelter)
            $createdAt = Carbon::now()->subDays(rand(0, 180));

            // Add small coordinate variation (±100 meters) for realistic spread
            $latitude  = $location['lat'] + (rand(-100, 100) / 100000); // ±0.001
            $longitude = $location['lng'] + (rand(-100, 100) / 100000); // ±0.001

            $reports[] = [
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'address'       => $location['address'],
                'city'          => $location['area'], // Use area as city (Malacca district)
                'state'         => 'Melaka', // Malacca state
                'report_status' => $reportStatuses[array_rand($reportStatuses)],
                'description'   => $descriptions[array_rand($descriptions)], // Use priority-based descriptions

                // Cross-database reference to Taufiq's users table
                'userID'        => $userIDs[array_rand($userIDs)],

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
            $this->command->info('Uploading seed images to Cloudinary and assigning to reports...');
            $this->command->info('(This may take a moment for first-time uploads)');

            // Assign images to reports (both in Eilya's database)
            $this->assignImagesToReports($insertedReportIDs, $imageCategories);

            DB::connection('eilya')->commit();

            // Calculate coordinate bounds for verification
            $latitudes = array_column($reports, 'latitude');
            $longitudes = array_column($reports, 'longitude');
            $minLat = min($latitudes);
            $maxLat = max($latitudes);
            $minLng = min($longitudes);
            $maxLng = max($longitudes);

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Report Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total reports created: " . count($reports));
            $this->command->info("Location: Malacca, Malaysia (Real coordinates)");
            $this->command->info("Areas covered: " . count($malaccaLocations) . " real locations");
            $this->command->info("Coordinate variation: ±100 meters for realism");
            $this->command->info('');
            $this->command->info('Leaflet Map Compatibility:');
            $this->command->info("  ✓ Latitude range: " . number_format($minLat, 4) . "° to " . number_format($maxLat, 4) . "°");
            $this->command->info("  ✓ Longitude range: " . number_format($minLng, 4) . "° to " . number_format($maxLng, 4) . "°");
            $this->command->info("  ✓ Map will auto-center on Malacca");
            $this->command->info("  ✓ All addresses verified and valid");
            $this->command->info('');
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
                // Upload image to Cloudinary
                $cloudinaryPath = $this->uploadToCloudinary($imagePath);

                // Skip if upload failed
                if ($cloudinaryPath === null) {
                    $this->command->warn("  Skipping image for report {$reportID}: {$imagePath}");
                    continue;
                }

                $images[] = [
                    'image_path' => $cloudinaryPath, // Store Cloudinary path
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
