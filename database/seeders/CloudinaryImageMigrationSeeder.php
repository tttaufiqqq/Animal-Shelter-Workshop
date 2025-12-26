<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CloudinaryImageMigrationSeeder extends Seeder
{
    /**
     * Cache for uploaded Cloudinary paths to avoid re-uploading the same image
     */
    private $cloudinaryCache = [];

    /**
     * Statistics tracking
     */
    private $stats = [
        'total' => 0,
        'uploaded' => 0,
        'already_cloudinary' => 0,
        'file_not_found' => 0,
        'upload_failed' => 0,
    ];

    /**
     * Run the migration seeder.
     *
     * This seeder migrates existing Image records from local storage to Cloudinary.
     * It can be run multiple times safely - images already on Cloudinary are skipped.
     *
     * Usage:
     * php artisan db:seed --class=CloudinaryImageMigrationSeeder
     */
    public function run()
    {
        $this->command->info('========================================');
        $this->command->info('Cloudinary Image Migration Seeder');
        $this->command->info('========================================');
        $this->command->newLine();

        // Check if Cloudinary is configured
        if (!$this->isCloudinaryConfigured()) {
            $this->command->error('❌ Cloudinary is not configured!');
            $this->command->error('Please set CLOUDINARY_URL in your .env file.');
            $this->command->newLine();
            $this->command->info('Required .env variables:');
            $this->command->info('  CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME');
            $this->command->info('  OR');
            $this->command->info('  CLOUDINARY_CLOUD_NAME=your-cloud-name');
            $this->command->info('  CLOUDINARY_API_KEY=your-api-key');
            $this->command->info('  CLOUDINARY_API_SECRET=your-api-secret');
            return;
        }

        $this->command->info('✓ Cloudinary configuration found');
        $this->command->newLine();

        // Get all images from Eilya database
        $images = DB::connection('eilya')->table('image')->get();

        if ($images->isEmpty()) {
            $this->command->warn('No images found in the database.');
            return;
        }

        $this->stats['total'] = $images->count();
        $this->command->info("Found {$this->stats['total']} images in the database");
        $this->command->info('Starting migration to Cloudinary...');
        $this->command->newLine();

        // Start transaction
        DB::connection('eilya')->beginTransaction();

        try {
            $progressBar = $this->command->getOutput()->createProgressBar($images->count());
            $progressBar->start();

            foreach ($images as $image) {
                $this->migrateImage($image);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->command->newLine(2);

            DB::connection('eilya')->commit();

            $this->displayResults();

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();
            $this->command->newLine(2);
            $this->command->error('❌ Migration failed: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');
            throw $e;
        }
    }

    /**
     * Migrate a single image record to Cloudinary
     */
    private function migrateImage($image)
    {
        $imagePath = $image->image_path;

        // Skip if already a Cloudinary path (doesn't contain storage paths)
        if ($this->isCloudinaryPath($imagePath)) {
            $this->stats['already_cloudinary']++;
            return;
        }

        // Attempt to upload to Cloudinary
        $cloudinaryPath = $this->uploadToCloudinary($imagePath);

        if ($cloudinaryPath === null) {
            // Upload failed (stats already updated in uploadToCloudinary)
            return;
        }

        // Update the database record
        DB::connection('eilya')
            ->table('image')
            ->where('id', $image->id)
            ->update([
                'image_path' => $cloudinaryPath,
                'updated_at' => now(),
            ]);

        $this->stats['uploaded']++;
    }

    /**
     * Upload a local image to Cloudinary
     * Returns the Cloudinary path (folder/filename), or null if upload fails
     */
    private function uploadToCloudinary($localPath)
    {
        // Check cache first
        if (isset($this->cloudinaryCache[$localPath])) {
            return $this->cloudinaryCache[$localPath];
        }

        try {
            // Determine the full path
            // Support both formats: "reports/cat1.jpg" and "storage/reports/cat1.jpg"
            $cleanPath = str_replace(['storage/', 'public/'], '', $localPath);
            $fullPath = storage_path('app/public/' . $cleanPath);

            // Check if file exists AND is readable
            if (!file_exists($fullPath) || !is_file($fullPath) || !is_readable($fullPath)) {
                $this->stats['file_not_found']++;
                return null;
            }

            // Extract folder and filename
            $folder = dirname($cleanPath); // 'reports' or 'animal_images'
            $fileName = pathinfo($cleanPath, PATHINFO_FILENAME); // Without extension

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

        } catch (\Cloudinary\Api\Exception\NotFound $e) {
            // File not found locally or in Cloudinary
            $this->stats['file_not_found']++;
            return null;
        } catch (\Exception $e) {
            // Other upload errors (network, credentials, etc.)
            $this->stats['upload_failed']++;
            return null;
        }
    }

    /**
     * Check if a path is already a Cloudinary path
     */
    private function isCloudinaryPath($path)
    {
        // Cloudinary paths don't contain these keywords
        $localKeywords = ['storage/', 'public/', 'app/', '.jpg', '.png', '.jpeg', '.gif'];

        // If path contains file extensions or storage paths, it's local
        foreach ($localKeywords as $keyword) {
            if (str_contains(strtolower($path), $keyword)) {
                return false;
            }
        }

        // If path follows pattern "folder/filename" without extensions, likely Cloudinary
        return true;
    }

    /**
     * Check if Cloudinary is configured
     */
    private function isCloudinaryConfigured()
    {
        return !empty(config('cloudinary.cloud_url')) &&
               config('cloudinary.cloud_url') !== 'cloudinary://';
    }

    /**
     * Display migration results
     */
    private function displayResults()
    {
        $this->command->info('========================================');
        $this->command->info('✓ Migration Completed!');
        $this->command->info('========================================');
        $this->command->newLine();

        $this->command->table(
            ['Status', 'Count'],
            [
                ['Total images in database', $this->stats['total']],
                ['✓ Successfully uploaded to Cloudinary', $this->stats['uploaded']],
                ['⊙ Already on Cloudinary (skipped)', $this->stats['already_cloudinary']],
                ['⚠ Local file not found', $this->stats['file_not_found']],
                ['✗ Upload failed', $this->stats['upload_failed']],
            ]
        );

        $this->command->newLine();

        // Display warnings if any
        if ($this->stats['file_not_found'] > 0) {
            $this->command->warn("⚠ {$this->stats['file_not_found']} local files were not found.");
            $this->command->warn("These images may have been deleted or moved.");
        }

        if ($this->stats['upload_failed'] > 0) {
            $this->command->warn("⚠ {$this->stats['upload_failed']} uploads failed.");
            $this->command->warn("Check your Cloudinary credentials and internet connection.");
        }

        $this->command->newLine();
        $this->command->info('Database: Eilya (MySQL)');
        $this->command->info('Images are now served from Cloudinary CDN');
        $this->command->info('========================================');
    }
}
