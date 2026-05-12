<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Compress and strictly resize an uploaded image natively using PHP GD to save disk space.
     * 
     * @param UploadedFile $file
     * @param string $disk
     * @param string $directory
     * @param int $maxWidth Max width before resizing (px)
     * @param int $quality JPEG/WebP compression quality (0-100)
     * @return string Path stored
     */
    public function optimizeAndStore(UploadedFile $file, string $directory = 'uploads', string $disk = 'public', int $maxWidth = 1080, int $quality = 70): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        // If it's not an image/supported, just save normally
        if (!in_array($extension, $allowed)) {
            return $file->store($directory, $disk);
        }

        $sourcePath = $file->getRealPath();
        list($originalWidth, $originalHeight) = getimagesize($sourcePath);

        // Native PHP GD Image Creation
        $image = null;
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $image = @imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            case 'webp':
                // Check if webp is supported by GD on this server
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($sourcePath);
                }
                break;
        }

        // If GD fails or format not supported, store original
        if (!$image) {
            return $file->store($directory, $disk);
        }

        // Calculate resize ratio
        if ($originalWidth > $maxWidth) {
            $ratio = $maxWidth / $originalWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) ($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        // Create canvas and resample
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($extension === 'png') {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Save optimized image temporarily
        $filename = Str::random(40) . '.jpg'; // Convert all heavy formats to optimized JPEG/Webp standard
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        
        imagejpeg($canvas, $tempPath, $quality);
        
        // Free up memory
        imagedestroy($image);
        imagedestroy($canvas);

        // Move to final storage disk
        $finalPath = $directory . '/' . $filename;
        Storage::disk($disk)->put($finalPath, file_get_contents($tempPath));
        
        // Clean temp
        @unlink($tempPath);

        return $finalPath;
    }
}
