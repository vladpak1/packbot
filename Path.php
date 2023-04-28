<?php
namespace PackBot;

/**
 * Local paths class.
 */
class Path {
    
    /**
     * Returns the path to the root directory of the project.
     * @return string
     */
    public static function toRoot(): string {
        return __DIR__;
    }

    public static function toLanguages(): string {
        return self::toRoot() . '/languages';
    }

    public static function toTemp(): string {
        $path = self::toRoot() . '/temp';
        if (!file_exists($path)) mkdir($path, 0777, true);
        return $path;
    }

    public static function toAssets(): string {
        return self::toRoot() . '/assets';
    }
}