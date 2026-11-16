<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Saves menu photos with GD: a copy at most 1200 px wide and a 400 px thumbnail,
 * both under generated names. Re-encoding also strips the camera metadata.
 */
class MenuPhotoStore
{
    public const FULL_WIDTH = 1200;

    public const THUMB_WIDTH = 400;

    private const DISK = 'public';

    /**
     * @return array{image_path: string, thumb_path: string}
     */
    public function store(UploadedFile $file): array
    {
        [$image, $extension] = $this->load($file);

        $name = Str::uuid()->toString().'.'.$extension;
        $paths = ['image_path' => 'menu/'.$name, 'thumb_path' => 'menu/thumbs/'.$name];

        $disk = Storage::disk(self::DISK);
        $disk->put($paths['image_path'], $this->encode($this->scaleToWidth($image, self::FULL_WIDTH), $extension));
        $disk->put($paths['thumb_path'], $this->encode($this->scaleToWidth($image, self::THUMB_WIDTH), $extension));

        return $paths;
    }

    /**
     * Removes a photo and its thumbnail. Missing or empty paths are skipped.
     */
    public function delete(?string ...$paths): void
    {
        $paths = array_values(array_filter($paths, fn (?string $path) => $path !== null && $path !== ''));

        if ($paths !== []) {
            Storage::disk(self::DISK)->delete($paths);
        }
    }

    /**
     * @return array{0: GdImage, 1: string}
     */
    private function load(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $info = $path === false ? false : @getimagesize($path);
        $extension = match ($info === false ? null : $info[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => null,
        };

        if ($path === false || $extension === null) {
            throw $this->unreadable();
        }

        $contents = file_get_contents($path);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw $this->unreadable();
        }

        return [$extension === 'jpg' ? $this->applyExifOrientation($image, $path) : $image, $extension];
    }

    private function unreadable(): ValidationException
    {
        return ValidationException::withMessages(['image' => __('staff-menu-photo-unreadable')]);
    }

    /**
     * Phone photos are often stored sideways with an EXIF hint; GD ignores it, so rotate here.
     */
    private function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $angle = match (is_array($exif) ? ($exif['Orientation'] ?? 1) : 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated === false ? $image : $rotated;
    }

    private function scaleToWidth(GdImage $source, int $maxWidth): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $targetWidth = max(1, min($width, $maxWidth));
        $targetHeight = max(1, (int) round($height * $targetWidth / $width));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($target === false) {
            throw $this->unreadable();
        }

        // Keep PNG and WebP transparency.
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, (int) imagecolorallocatealpha($target, 0, 0, 0, 127));

        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $target;
    }

    private function encode(GdImage $image, string $extension): string
    {
        ob_start();

        match ($extension) {
            'png' => imagepng($image, null, 6),
            'webp' => imagewebp($image, null, 80),
            default => imagejpeg($image, null, 82),
        };

        return (string) ob_get_clean();
    }
}
