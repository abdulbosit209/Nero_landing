<?php

declare(strict_types=1);

namespace app\components;

use RuntimeException;

/**
 * Shrinks uploaded photos to fit under a byte-size budget before they're relayed to
 * Telegram. Phone cameras routinely produce 8-15MB JPEGs at resolutions no chat client
 * ever displays at full size, so downscaling to a sane max dimension first — then
 * stepping JPEG quality down only as far as needed — gets well under budget with no
 * perceptible quality loss in the common case.
 */
final class ImageCompressor
{
    private const MAX_DIMENSION = 2000;
    private const START_QUALITY = 85;
    private const MIN_QUALITY = 40;
    private const QUALITY_STEP = 10;

    /**
     * @return string binary contents of a JPEG guaranteed to be as close to $maxBytes
     *  as this strategy can get; if even the lowest quality step still exceeds it, the
     *  smallest encoding found is returned rather than failing outright.
     */
    public static function compress(string $path, int $maxBytes = 2 * 1024 * 1024): string
    {
        $original = file_get_contents($path);
        if ($original === false) {
            throw new RuntimeException("Unable to read image at $path");
        }

        if (strlen($original) <= $maxBytes) {
            return $original;
        }

        $image = @imagecreatefromstring($original);
        if ($image === false) {
            // Not a raster format GD can decode (shouldn't happen given LeadForm's
            // extension whitelist) — pass the original through rather than fail the send.
            return $original;
        }

        $image = self::resizeToFit($image);

        $best = null;
        for ($quality = self::START_QUALITY; $quality >= self::MIN_QUALITY; $quality -= self::QUALITY_STEP) {
            $encoded = self::encode($image, $quality);
            $best = $encoded;

            if (strlen($encoded) <= $maxBytes) {
                break;
            }
        }

        imagedestroy($image);

        return $best;
    }

    /**
     * @param \GdImage $image
     * @return \GdImage
     */
    private static function resizeToFit($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longestSide = max($width, $height);

        if ($longestSide <= self::MAX_DIMENSION) {
            return $image;
        }

        $scale = self::MAX_DIMENSION / $longestSide;
        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    /**
     * @param \GdImage $image
     */
    private static function encode($image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);

        return ob_get_clean();
    }
}
