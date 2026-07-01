<?php

namespace Database\Seeders\Concerns\Shared;

trait UploadsCloudinaryImages
{
    private array $cloudinaryCache = [];

    private function uploadToCloudinary(string $localPath): ?string
    {
        if (isset($this->cloudinaryCache[$localPath])) {
            return $this->cloudinaryCache[$localPath];
        }

        try {
            $fullPath = storage_path('app/public/' . $localPath);

            if (!file_exists($fullPath)) {
                $this->command->warn("  Image not found: {$localPath}");
                return null;
            }

            $folder   = dirname($localPath);
            $fileName = pathinfo($localPath, PATHINFO_FILENAME);

            cloudinary()->uploadApi()->upload($fullPath, [
                'folder'    => $folder,
                'public_id' => $fileName,
            ]);

            $cloudinaryPath = $folder . '/' . $fileName;
            $this->cloudinaryCache[$localPath] = $cloudinaryPath;

            return $cloudinaryPath;

        } catch (\Exception $e) {
            $this->command->error("  Failed to upload {$localPath}: " . $e->getMessage());
            return null;
        }
    }
}
