<?php

namespace PackBot;

use DateTime;

/**
 * @deprecated Use Time class instead!
 */
trait TimeTrait
{
    protected Text $text;

    /**
     * Method to get time difference between two timestamps.
     */
    protected function getTimeDiff($timestamp1, $timestamp2)
    {
        return abs($timestamp1 - $timestamp2);
    }

    /**
     * Method to translate timestamp into readable time.
     * Takes into account regional peculiarities in writing time.
     */
    protected function getReadableTime(string $timestamp): string
    {
        $datetime = new DateTime("@$timestamp");

        return match($this->text->getCurrentLanguage()) {
            'ru_RU' => $datetime->format('d.m.Y H:i:s'),
            'en_US' => $datetime->format('m/d/Y h:i:s A'),
            default => $datetime->format('m/d/Y h:i:s A'),
        };
    }

    /**
     * The function outputs a relative time string that includes years,
     * months, and days.
     * The language of this string is determined by the user's settings.
     */
    protected function getShortRelativeTime(int $timestamp): string
    {
        $lang = $this->text->getCurrentLanguage();

        if ('ru_RU' == $lang) {
            return $this->getShortRelativeTimeRussian($timestamp);
        } elseif ('en_US' == $lang) {
            return $this->getShortRelativeTimeEnglish($timestamp);
        }
        // return $this->getRelativeTimeEnglish($timestamp, false);

    }

    /**
     * The function outputs a relative time string that includes years,
     * months, and days.
     * The language of this string is determined by the user's settings.
     */
    protected function getRelativeTime(int $timestamp): string
    {
        $lang = $this->text->getCurrentLanguage();

        if ('ru_RU' == $lang) {
            return $this->getRelativeTimeRussian($timestamp);
        } elseif ('en_US' == $lang) {
            return $this->getRelativeTimeEnglish($timestamp);
        }
    }

    /**
     * Calculates how much time has passed
     * since the specified timestamp in other time units.
     *
     * NOTE: time values are isolated from each other, they
     * display a COMPLETE representation of the time stamp
     * diffrence in diffrent .
     *
     * @param  int   $timestamp Required. The timestamp.
     * @return array $diff The time diffrence. {
     * @type   int   $diff['seconds']
     * @type   int   $diff['minutes']
     * @type   int   $diff['hours']
     * @type   int   $diff['days']
     * @type   int   $diff['months']
     *                         }
     */
    protected function getTimestampsDifference(int $timestamp): array
    {
        $firstDateTime = new DateTime();
        $firstDateTime->setTimestamp($timestamp);

        $secondDateTime = new DateTime();
        $diff           = $secondDateTime->getTimestamp() - $timestamp; //diff in seconds.

        $months  = 0;
        $days    = 0;
        $hours   = 0;
        $minutes = 0;
        $seconds = 0;

        /**
         * Get time units separately.
         */
        $seconds = $diff;

        /**
         * Mins.
         */
        if ($seconds >= 60) {
            $minutes = floor($seconds / 60);
        }

        /**
         * Hours.
         */
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
        }

        /**
         * Days.
         */
        if ($hours >= 24) {
            $days = floor($hours / 24);
        }

        /**
         * Months.
         * (30 days).
         */
        if ($days >= 30) {
            $months = floor($days / 30);
        }

        return [
            'seconds' => $seconds,
            'minutes' => $minutes,
            'hours'   => $hours,
            'days'    => $days,
            'months'  => $months,
        ];
    }

    /**
     * Converts seconds into human readable format.
     */
    protected function secondsToHumanReadable(int $seconds): string
    {
        $lang = $this->text->getCurrentLanguage();

        if ('ru_RU' == $lang) {
            return $this->secondsToHumanReadableRussian($seconds);
        } elseif ('en_US' == $lang) {
            return $this->secondsToHumanReadableEnglish($seconds);
        }

        return $this->secondsToHumanReadableEnglish($seconds);

    }

    private function secondsToHumanReadableRussian(int $seconds): string
    {
        $minutes          = intval($seconds / 60);
        $remainingSeconds = $seconds % 60;
        $minuteWord       = $this->declension($minutes, ['минута', 'минуты', 'минут']);
        $secondWord       = $this->declension($remainingSeconds, ['секунда', 'секунды', 'секунд']);

        return "{$minutes} {$minuteWord} {$remainingSeconds} {$secondWord}";
    }

    private function secondsToHumanReadableEnglish(int $seconds): string
    {
        $minutes          = intval($seconds / 60);
        $remainingSeconds = $seconds % 60;
        $minuteWord       = $this->declension($minutes, ['minute', 'minutes', 'minutes']);
        $secondWord       = $this->declension($remainingSeconds, ['second', 'seconds', 'seconds']);

        return "{$minutes} {$minuteWord} {$remainingSeconds} {$secondWord}";
    }

    /**
     * hours, minutes.
     */
    private function getRelativeTimeRussian(int $timestamp): string
    {
        if (0 == $timestamp) {
            return 'никогда';
        }
        $now     = time();
        $diff    = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $hours   = $diff->h;
        $minutes = $diff->i;

        $result = '';

        if ($hours > 0) {
            $result .= $hours . ' ' . $this->declension($hours, ['час', 'часа', 'часов']) . ' ';
        }

        if ($minutes > 0) {
            $result .= $minutes . ' ' . $this->declension($minutes, ['минута', 'минуты', 'минут']) . ' ';
        }

        /**
         * Check if the timestamp is in the future or in the past.
         */
        if ($timestamp > $now) {
            $result = 'через ' . $result;
        } else {
            $result = $result . 'назад';
        }

        if ('назад' == $result) {
            return 'только что';
        }

        if ('1 минута назад' == $result) {
            return 'минуту назад';
        }

        return $result;
    }

    /**
     * hours, minutes.
     */
    private function getRelativeTimeEnglish(int $timestamp): string
    {
        if (0 == $timestamp) {
            return 'never';
        }
        $now     = time();
        $diff    = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $hours   = $diff->h;
        $minutes = $diff->i;

        $result = '';

        if ($hours > 0) {
            $result .= $hours . ' hour' . ($hours > 1 ? 's ' : ' ');
        }

        if ($minutes > 0) {
            $result .= $minutes . ' minute' . ($minutes > 1 ? 's ' : ' ');
        }

        /**
         * Check if the timestamp is in the future or in the past.
         */
        if ($timestamp > $now) {
            $result = 'in ' . $result;
        } else {
            $result = $result . 'ago';
        }

        if ('ago' == $result) {
            return 'just now';
        }

        return $result;
    }

    private function getShortRelativeTimeRussian(int $timestamp): string
    {
        $now    = time();
        $diff   = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $years  = $diff->y;
        $months = $diff->m;
        $days   = $diff->d;

        $result = '';

        if ($years > 0) {
            $result .= $years . ' ' . $this->declension($years, ['год', 'года', 'лет']) . ' ';
        }

        if ($months > 0) {
            $result .= $months . ' ' . $this->declension($months, ['месяц', 'месяца', 'месяцев']) . ' ';
        }

        if ($days > 0) {
            $result .= $days . ' ' . $this->declension($days, ['день', 'дня', 'дней']) . ' ';
        }

        /**
         * Check if the timestamp is in the future or in the past.
         */
        if ($timestamp > $now) {
            $result = 'через ' . $result;
        } else {
            $result = $result . 'назад';
        }

        return $result;
    }

    private function getShortRelativeTimeEnglish(int $timestamp): string
    {
        $now    = time();
        $diff   = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $years  = $diff->y;
        $months = $diff->m;
        $days   = $diff->d;

        $result = '';

        if ($years > 0) {
            $result .= $years . ' year' . ($years > 1 ? 's ' : ' ');
        }

        if ($months > 0) {
            $result .= $months . ' month' . ($months > 1 ? 's ' : ' ');
        }

        if ($days > 0) {
            $result .= $days . ' day' . ($days > 1 ? 's ' : ' ');
        }

        /**
         * Check if the timestamp is in the future or in the past.
         */
        if ($timestamp > $now) {
            $result = 'in ' . $result;
        } else {
            $result = $result . 'ago';
        }

        return $result;
    }

    private function declension($number, $words)
    {
        $number = abs($number);

        if ($number > 20) {
            $number %= 10;
        }

        if (1 == $number) {
            return $words[0];
        }

        if ($number >= 2 && $number <= 4) {
            return $words[1];
        }

        return $words[2];
    }
}
