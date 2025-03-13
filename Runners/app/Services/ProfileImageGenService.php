<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickException;
use Multiavatar;

class ProfileImageGenService
{
    public function generateImage(string $seed): string
    {
        try {
            $svg = $this->generateSVG($seed);

            return $this->saveImageFile($svg);
        } catch (ImagickException) {
            return '';
        }
    }

    private function generateSvg(string $seed): string
    {
        $multiAvatar = new Multiavatar;

        return $multiAvatar($seed, null, null);
    }

    /**
     * @throws ImagickException
     */
    private function saveImageFile(string $svgString): string
    {
        $filename = md5($svgString).'.png';

        $imagePath = Storage::disk('processing')->path($filename);

        // Create a new Imagick object
        $imagick = new Imagick;

        // Read the SVG string
        $imagick->readImageBlob($svgString);

        // Set the format to PNG
        $imagick->setImageFormat('png');

        // Save the PNG file
        $imagick->writeImage($imagePath);

        // Clear the Imagick object
        $imagick->clear();

        return $imagePath;
    }
}
