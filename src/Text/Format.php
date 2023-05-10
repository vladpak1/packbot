<?php

namespace PackBot;

class Format
{
    public static function deleteSpaces(string $string): string
    {
        return preg_replace('/\s+/', '', $string);
    }

    public static function deleteBTag(string $string): string
    {
        return preg_replace('/<b>|<\/b>/', '', $string);
    }

    /**
     * Prepares string for safe sending to the telegram.
     */
    public static function prepDisplay(string $string, $maxLength = 0): string
    {
        if (0 == $maxLength) {
            return htmlspecialchars($string);
        }

        $string = htmlspecialchars($string);

        if (strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength - 3) . '...';
        }

        return $string;
    }
}
