<?php

/**
 * Global helper functions for the Animal Shelter application
 */

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
            if (!app(\App\Services\DatabaseConnectionChecker::class)->isConnected('eilya')) {
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
            if (!app(\App\Services\DatabaseConnectionChecker::class)->isConnected('eilya')) {
                return collect([]);
            }

            return $imagesRelation->get();
        } catch (\Exception $e) {
            \Log::error("Failed to load images from Eilya database: " . $e->getMessage());
            return collect([]);
        }
    }
}

if (!function_exists('isDatabaseOnline')) {
    /**
     * Check if a database connection is online
     *
     * @param string $connection Connection name (e.g., 'eilya', 'danish', etc.)
     * @return bool
     */
    function isDatabaseOnline($connection)
    {
        try {
            return app(\App\Services\DatabaseConnectionChecker::class)->isConnected($connection);
        } catch (\Exception $e) {
            \Log::error("Failed to check database connection status: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('getServerIpAddress')) {
    /**
     * Get the actual IPv4 address of the server/machine
     * Works cross-platform (Windows, Linux, macOS)
     * Prioritizes institutional/corporate network IPs (10.x.x.x) over virtual adapters
     *
     * @return string|null
     */
    function getServerIpAddress(): ?string
    {
        try {
            // Method 1: Try to get from $_SERVER (works in some environments)
            if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' && $_SERVER['SERVER_ADDR'] !== '::1') {
                return $_SERVER['SERVER_ADDR'];
            }

            // Method 2: Use system command based on OS
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: Use ipconfig to get ALL IPv4 addresses
                $output = shell_exec('ipconfig');
                if ($output) {
                    // Match ALL IPv4 Address patterns
                    preg_match_all('/IPv4 Address[.\s]*:\s*([0-9.]+)/', $output, $matches);

                    if (!empty($matches[1])) {
                        $ips = $matches[1];

                        // Prioritize IPs by network type
                        $bestIp = null;
                        $priority = 999;

                        foreach ($ips as $ip) {
                            // Skip loopback
                            if ($ip === '127.0.0.1') {
                                continue;
                            }

                            $currentPriority = 999;

                            // Priority 1: Institutional networks (10.x.x.x)
                            if (preg_match('/^10\./', $ip)) {
                                $currentPriority = 1;
                            }
                            // Priority 2: Private networks (172.16.x.x - 172.31.x.x)
                            elseif (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
                                $currentPriority = 2;
                            }
                            // Priority 3: Other non-192.168 addresses
                            elseif (!preg_match('/^192\.168\./', $ip)) {
                                $currentPriority = 3;
                            }
                            // Priority 4: Home networks (192.168.x.x) - but exclude VirtualBox ranges
                            elseif (preg_match('/^192\.168\./', $ip)) {
                                // Skip common virtual adapter IPs
                                if (preg_match('/^192\.168\.(56|100)\./', $ip)) {
                                    $currentPriority = 999; // Very low priority
                                } else {
                                    $currentPriority = 4;
                                }
                            }

                            // Use the IP with the highest priority (lowest number)
                            if ($currentPriority < $priority) {
                                $priority = $currentPriority;
                                $bestIp = $ip;
                            }
                        }

                        if ($bestIp) {
                            return $bestIp;
                        }
                    }
                }
            } else {
                // Linux/macOS: Use hostname -I
                $output = shell_exec('hostname -I 2>/dev/null');
                if ($output) {
                    // Get all IPs from the output
                    $ips = explode(' ', trim($output));

                    $bestIp = null;
                    $priority = 999;

                    foreach ($ips as $ip) {
                        // Only process IPv4 addresses
                        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || $ip === '127.0.0.1') {
                            continue;
                        }

                        $currentPriority = 999;

                        // Priority 1: Institutional networks (10.x.x.x)
                        if (preg_match('/^10\./', $ip)) {
                            $currentPriority = 1;
                        }
                        // Priority 2: Private networks (172.16.x.x - 172.31.x.x)
                        elseif (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
                            $currentPriority = 2;
                        }
                        // Priority 3: Other non-192.168 addresses
                        elseif (!preg_match('/^192\.168\./', $ip)) {
                            $currentPriority = 3;
                        }
                        // Priority 4: Home networks (192.168.x.x)
                        elseif (preg_match('/^192\.168\./', $ip)) {
                            $currentPriority = 4;
                        }

                        if ($currentPriority < $priority) {
                            $priority = $currentPriority;
                            $bestIp = $ip;
                        }
                    }

                    if ($bestIp) {
                        return $bestIp;
                    }
                }
            }

            // Method 3: PHP fallback using gethostbyname
            $hostname = gethostname();
            if ($hostname !== false) {
                $ip = gethostbyname($hostname);
                if ($ip !== $hostname && $ip !== '127.0.0.1') {
                    return $ip;
                }
            }

            // If all methods fail, return null
            return null;
        } catch (\Exception $e) {
            \Log::error("Failed to get server IP address: " . $e->getMessage());
            return null;
        }
    }
}
