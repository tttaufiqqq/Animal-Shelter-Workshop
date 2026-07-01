<?php

if (!function_exists('getAnimalImageOrPlaceholder')) {
    /**
     * Get the first image URL for an animal, or return placeholder if unavailable
     *
     * @param \App\Models\Animal|null $animal
     * @param string $placeholder Path to placeholder image (relative to public/)
     * @return string Full asset URL
     */
    function getAnimalImageOrPlaceholder($animal, $placeholder = 'images/placeholder-animal.svg')
    {
        if (!$animal) {
            return asset($placeholder);
        }

        try {
            // Use the model's safe method
            $imagePath = $animal->getFirstImageOrPlaceholder();
            return asset($imagePath);
        } catch (\Exception $e) {
            \Log::error("Failed to get animal image: " . $e->getMessage());
            return asset($placeholder);
        }
    }
}

if (!function_exists('getReportImageOrPlaceholder')) {
    /**
     * Get the first image URL for a stray report, or return placeholder if unavailable
     *
     * @param \App\Models\Report|null $report
     * @param string $placeholder Path to placeholder image (relative to public/)
     * @return string Full asset URL
     */
    function getReportImageOrPlaceholder($report, $placeholder = 'images/placeholder-animal.svg')
    {
        if (!$report) {
            return asset($placeholder);
        }

        try {
            // Check if Eilya database is available
            if (!app(\App\Services\DatabaseConnectionChecker::class)->isConnected('reporting')) {
                return asset($placeholder);
            }

            $images = $report->images()->get();

            if ($images->isNotEmpty()) {
                return asset('storage/' . $images->first()->image_path);
            }

            return asset($placeholder);
        } catch (\Exception $e) {
            \Log::error("Failed to get report image: " . $e->getMessage());
            return asset($placeholder);
        }
    }
}

if (!function_exists('safeLoadImages')) {
    /**
     * Safely load images from Eilya database with fallback to empty collection
     *
     * @param \Illuminate\Database\Eloquent\Relations\HasMany $imagesRelation
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function safeLoadImages($imagesRelation)
    {
        try {
            // Check if Eilya database is available
            if (!app(\App\Services\DatabaseConnectionChecker::class)->isConnected('reporting')) {
                return collect([]);
            }

            return $imagesRelation->get();
        } catch (\Exception $e) {
            \Log::error("Failed to load images from Eilya database: " . $e->getMessage());
            return collect([]);
        }
    }
}
