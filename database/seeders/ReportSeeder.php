<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Concerns\Shared\UploadsCloudinaryImages;
use Database\Seeders\Concerns\ReportSeeder\ProvidesLocationData;
use Database\Seeders\Concerns\ReportSeeder\AssignsReportImages;

class ReportSeeder extends Seeder
{
    use UploadsCloudinaryImages, ProvidesLocationData, AssignsReportImages;

    public function run(): void
    {
        $this->command->info('Starting Report Seeder (Malacca, Malaysia)...');
        $this->command->info('========================================');

        $malaccaLocations = $this->getMalaccaLocations();
        $this->command->info('Loaded ' . count($malaccaLocations) . ' real Malacca locations');

        $this->command->info("Fetching eligible users from Taufiq's database...");
        $excludedRoles = ['admin', 'caretaker'];
        $userIDs = User::whereDoesntHave('roles', function ($q) use ($excludedRoles) {
            $q->whereIn('name', $excludedRoles);
        })->pluck('id')->toArray();

        if (empty($userIDs)) {
            $this->command->error('No eligible users found!');
            return;
        }
        $this->command->info("Found " . count($userIDs) . " eligible users");

        $reportStatuses = ['Pending', 'Assigned', 'In Progress', 'Completed', 'Rejected'];
        $descriptions   = [
            'Injured animal - Critical condition',
            'Trapped animal - Immediate rescue needed',
            'Aggressive animal - Public safety risk',
            'Sick animal - Needs medical attention',
            'Mother with puppies/kittens - Family rescue',
            'Young animal (puppy/kitten) - Vulnerable',
            'Malnourished animal - Needs care',
            'Healthy stray - Needs rescue',
            'Abandoned pet - Recent',
            'Friendly stray - Approachable',
        ];

        $imageCategories = ['cat' => [], 'dog' => [], 'dogcat' => []];
        for ($i = 1; $i <= 3; $i++) { $imageCategories['cat'][]    = "reports/cat{$i}.jpg"; }
        for ($i = 1; $i <= 6; $i++) { $imageCategories['dog'][]    = "reports/dog{$i}.jpg"; }
        for ($i = 1; $i <= 3; $i++) { $imageCategories['dogcat'][] = "reports/dogcat{$i}.jpg"; }

        $reports = [];
        $this->command->info('Preparing 200 reports with Malacca locations...');

        for ($i = 0; $i < 200; $i++) {
            $location  = $malaccaLocations[array_rand($malaccaLocations)];
            $createdAt = Carbon::now()->subDays(rand(0, 180));
            $latitude  = $location['lat'] + (rand(-100, 100) / 100000);
            $longitude = $location['lng'] + (rand(-100, 100) / 100000);

            $reports[] = [
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'address'       => $location['address'],
                'city'          => $location['area'],
                'state'         => 'Melaka',
                'report_status' => $reportStatuses[array_rand($reportStatuses)],
                'description'   => $descriptions[array_rand($descriptions)],
                'userID'        => $userIDs[array_rand($userIDs)],
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }

        DB::connection('reporting')->beginTransaction();
        try {
            $this->command->info("Inserting reports into Eilya's database...");
            $totalInserted = 0;
            foreach (array_chunk($reports, 100) as $chunk) {
                DB::connection('reporting')->table('report')->insert($chunk);
                $totalInserted += count($chunk);
                $this->command->info("  Inserted {$totalInserted} / " . count($reports) . " reports...");
            }

            $insertedReportIDs = DB::connection('reporting')
                ->table('report')->orderBy('id', 'desc')->limit(600)->pluck('id')->toArray();

            $this->command->info('');
            $this->command->info('Uploading seed images to Cloudinary and assigning to reports...');
            $this->command->info('(This may take a moment for first-time uploads)');
            $this->assignImagesToReports($insertedReportIDs, $imageCategories);

            DB::connection('reporting')->commit();

            $latitudes  = array_column($reports, 'latitude');
            $longitudes = array_column($reports, 'longitude');

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Report Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total reports created: " . count($reports));
            $this->command->info("Location: Malacca, Malaysia (Real coordinates)");
            $this->command->info("Areas covered: " . count($malaccaLocations) . " real locations");
            $this->command->info("Latitude range: " . number_format(min($latitudes), 4) . "° to " . number_format(max($latitudes), 4) . "°");
            $this->command->info("Longitude range: " . number_format(min($longitudes), 4) . "° to " . number_format(max($longitudes), 4) . "°");
            $this->command->info("Database: Eilya (MySQL) | User references: Taufiq (PostgreSQL)");
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('reporting')->rollBack();
            $this->command->error('Error seeding reports: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');
            throw $e;
        }
    }
}
