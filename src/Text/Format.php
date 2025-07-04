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
    public static function prepDisplay(string $string, int $maxLength = 0): string
    {
        // 1. Убираем пробелы по краям
        $string = trim($string);

        // 2. Если ограничение не задано — просто экранируем
        if ($maxLength <= 0) {
            return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // 3. Укорачиваем по символам, а не по байтам
        if (mb_strlen($string, 'UTF-8') > $maxLength) {
            // mb_strimwidth режет по *ширине* и сам добавит «…»
            $string = mb_strimwidth($string, 0, $maxLength, '…', 'UTF-8');
        }

        // 4. Экранируем уже готовую финальную строку
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
