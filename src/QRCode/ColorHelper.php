<?php

namespace PackBot\QRCode;

class ColorHelper
{
    public static function getMostCommonColors(\Intervention\Image\Image $image): array
    {
        $resizedImage = $image->resize(50, 50); // Resize the image so we can iterate over it faster

        $colorFrequency = [];

        // Iterate through the resized image's pixels and count color frequencies
        for ($x = 0; $x < $resizedImage->width(); $x++) {
            for ($y = 0; $y < $resizedImage->height(); $y++) {
                $color = $resizedImage->pickColor($x, $y, 'hex');

                if (!isset($colorFrequency[$color])) {
                    $colorFrequency[$color] = 0;
                }
                $colorFrequency[$color]++;
            }
        }

        arsort($colorFrequency);

        // Get the 5 most common colors
        $mostCommonColors = array_slice(array_keys($colorFrequency), 0, 5);

        return array_values($mostCommonColors);
    }

    public static function getALittleDarkerColor(string $hexColor): string
    {
        $color = str_replace('#', '', $hexColor);

        if (6 != strlen($color)) {
            return '#000000';
        }

        $rgb = [];

        for ($x = 0; $x < 3; $x++) {
            $rgb[$x] = hexdec(substr($color, (2 * $x), 2));
        }

        $darkerColor = '#';

        for ($x = 0; $x < 3; $x++) {
            $darkerColor .= str_pad(dechex(round($rgb[$x] * 0.8)), 2, 0, STR_PAD_LEFT);
        }

        return $darkerColor;
    }

    public static function getALittleLighterColor(string $hexColor): string
    {
        $color = str_replace('#', '', $hexColor);

        if (6 != strlen($color)) {
            return '#FFFFFF';
        }

        $rgb = [];

        for ($x = 0; $x < 3; $x++) {
            $rgb[$x] = hexdec(substr($color, (2 * $x), 2));
        }

        $lighterColor = '#';

        for ($x = 0; $x < 3; $x++) {
            $adjustedValue = round($rgb[$x] * 1.2);
            $maxValue      = min($adjustedValue, 255);
            $lighterColor .= str_pad(dechex($maxValue), 2, 0, STR_PAD_LEFT);
        }

        return $lighterColor;
    }

    public static function hexToRgbArray(string $hex): array
    {
        $hex = str_replace('#', '', $hex);

        if (6 != strlen($hex)) {
            return [0, 0, 0];
        }

        $rgb = [];

        for ($x = 0; $x < 3; $x++) {
            $rgb[$x] = hexdec(substr($hex, (2 * $x), 2));
        }

        return $rgb;
    }

    public static function isTooLight(array $rgb): bool
    {
        $luminance = (0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2]) / 255;

        return $luminance > 0.5;
    }
}
