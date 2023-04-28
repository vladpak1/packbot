<?php

namespace PackBot;

use DateTime;
use IntlDateFormatter;

class Time {

    protected Text $text;

    /**
     * This class is used to work with time.
     */
    public function __construct() {
        $this->text = new Text();
    }

    /**
     * A universal function for converting a timestamp to human-readable relative time.
     * Supports all units of time, but includes them in the string only when necessary (for example, 1 minute 20 seconds or 2 days 3 hours, 5 days).
     * Optionally can add the word "back" or "in".
     * 
     * @param int $timestamp Required. The timestamp.
     * @param bool $addBackIn Optional. Whether to add the word "back" or "in" to the beginning of the string. Default: false.
     */
    public function timestampToUltimateHumanReadableRelativeTime(int $timestamp, bool $addBackIn = true): string {

        if ($timestamp == 0) return $this->text->e('никогда');

        $lang = $this->text->getCurrentLanguage();
        $now = time();
        $diff = date_diff(new DateTime("@$timestamp"), new DateTime("@$now"));

        $years   = $diff->y;
        $months  = $diff->m;
        $days    = $diff->d;
        $hours   = $diff->h;
        $minutes = $diff->i;
        $seconds = $diff->s;

        switch($lang) {
            case 'ru_RU':
                $yearWord   = $this->declension($years, array('год', 'года', 'лет'));
                $monthWord  = $this->declension($months, array('месяц', 'месяца', 'месяцев'));
                $dayWord    = $this->declension($days, array('день', 'дня', 'дней'));
                $hourWord   = $this->declension($hours, array('час', 'часа', 'часов'));
                $minuteWord = $this->declension($minutes, array('минута', 'минуты', 'минут'));
                $secondWord = $this->declension($seconds, array('секунда', 'секунды', 'секунд'));
                break;
            case 'en_US':
                $yearWord   = $this->declension($years, array('year', 'years', 'years'));
                $monthWord  = $this->declension($months, array('month', 'months', 'months'));
                $dayWord    = $this->declension($days, array('day', 'days', 'days'));
                $hourWord   = $this->declension($hours, array('hour', 'hours', 'hours'));
                $minuteWord = $this->declension($minutes, array('minute', 'minutes', 'minutes'));
                $secondWord = $this->declension($seconds, array('second', 'seconds', 'seconds'));
                break;
        }

        /**
         * We select the most ideal time string with respect to time depending on time.
         */
        if ($years > 3) {
            return "{$years} {$yearWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($years <= 3 && $years != 0) {
            return "{$years} {$yearWord} {$months} {$monthWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($months > 3) {
            return "{$months} {$monthWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($months <= 3 && $months != 0) {
            $monthString = "{$months} {$monthWord}";
            $dayString = $days != 0 ? " {$days} {$dayWord}" : "";
            return $monthString . $dayString . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($days > 3) {
            return "{$days} {$dayWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($days <= 3 && $days != 0) {
            if ($days == 1 && $hours == 0) return $this->text->e('вчера');
            $dayString = "{$days} {$dayWord}";
            $hourString = $hours != 0 ? " {$hours} {$hourWord}" : "";
            return $dayString . $hourString . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($hours > 3) {
            return "{$hours} {$hourWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($hours <= 3 && $hours != 0) {
            $hourString = "{$hours} {$hourWord}";
            $minuteString = $minutes != 0 ? " {$minutes} {$minuteWord}" : "";
            return $hourString . $minuteString . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($minutes > 3) {
            return "{$minutes} {$minuteWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($minutes <= 3 && $minutes != 0) {
            $minuteString = "{$minutes} {$minuteWord}";
            $secondString = $seconds != 0 ? " {$seconds} {$secondWord}" : "";
            return $minuteString . $secondString . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }
        if ($minutes == 0) {
            return "{$seconds} {$secondWord}" . ($addBackIn ? ' ' . $this->text->e('назад') : '');
        }

        return 'error';

    }

    /**
     * @internal
     */
    public function relative_time_string($timestamp) {
        $now = new DateTime();
        $timestampDate = new DateTime();
        $timestampDate->setTimestamp($timestamp);
        $interval = $now->diff($timestampDate);
    
        $years   = $interval->y;
        $months  = $interval->m;
        $days    = $interval->d;
        $hours   = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;
    
        $relative_time_string = sprintf(
            "%d years %d months %d days %d hours %d minutes %d seconds ago",
            $years, $months, $days, $hours, $minutes, $seconds
        );
    
        return $relative_time_string;
    }

    public function DateTimeToTimestamp($datetime) {
        $datetime = new DateTime($datetime);
        return $datetime->getTimestamp();
    }

    public function convertMySQLDateTimeToHumanReadableDateTime(string $dateTime): string {
        $dateTime = date_create_from_format('Y-m-d H:i:s', $dateTime);
        $lang = $this->text->getCurrentLanguage();
        $dateFormat = match($lang) {
            'ru_RU' => 'd MMMM Y',
            'en_US' => 'MMMM d, Y',
            default => 'd MMMM Y'
        };
        $timeFormat = match($lang) {
            'ru_RU' => 'H:i:s',
            'en_US' => 'h:i:s A',
            default => 'H:i:s'
        };
        
        $formatter = new IntlDateFormatter(
            $this->text->getCurrentLanguage(),
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            $dateFormat
        );

        $formattedDate = $formatter->format($dateTime);
        $formattedTime = $dateTime->format($timeFormat);

        return $formattedDate . ' ' . $this->text->e('в') . ' ' . $formattedTime;
    }

    public function getServerTimezone(): string {
        $zone = date_default_timezone_get();
        if ($zone == 'Europe/Moscow') return 'MSK';
        return $zone;
    }

    /**
     * Method to get time difference between two timestamps.
     */
    public function getTimeDiff($timestamp1, $timestamp2) {
        return abs($timestamp1 - $timestamp2);
    }

    public function getTimestampFromDatetime(string $datetime) {
        $datetime = new DateTime($datetime);
        return $datetime->getTimestamp();
    }
      
    /**
     * Method to translate timestamp into readable time.
     * Takes into account regional peculiarities in writing time.
     */
    public function getReadableTime(int $timestamp): string {
        $datetime = new DateTime("@$timestamp");

        return match($this->text->getCurrentLanguage()) {
            "ru_RU" => $datetime->format('d.m.Y H:i:s'),
            "en_US" => $datetime->format('m/d/Y h:i:s A'),
            default => $datetime->format('m/d/Y h:i:s A'),
        };
    }

    /**
     * The function outputs a relative time string that includes years,
     * months, and days.
     * The language of this string is determined by the user's settings.
     */
    public function getShortRelativeTime(int $timestamp): string {
        $lang = $this->text->getCurrentLanguage();

        if ($lang == 'ru_RU') {
            return $this->getShortRelativeTimeRussian($timestamp);
        } elseif ($lang == 'en_US') {
            return $this->getShortRelativeTimeEnglish($timestamp);
        } else {
            // return $this->getRelativeTimeEnglish($timestamp, false);
        }
    }
    
    /**
     * The function outputs a relative time string that includes years,
     * months, and days.
     * The language of this string is determined by the user's settings.
     */
    public function getRelativeTime(int $timestamp): string {
        $lang = $this->text->getCurrentLanguage();

        if ($lang == 'ru_RU') {
            return $this->getRelativeTimeRussian($timestamp);
        } elseif ($lang == 'en_US') {
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
     * @param int $timestamp Required. The timestamp.
     * @return array $diff The time diffrence. {
     * @type int $diff['seconds']
     * @type int $diff['minutes']
     * @type int $diff['hours']
     * @type int $diff['days']
     * @type int $diff['months']
     * }
     */
    public function getTimestampsDifference(int $timestamp): array {
        $firstDateTime = new DateTime();
        $firstDateTime->setTimestamp($timestamp);

        $secondDateTime = new DateTime();
        $diff = $secondDateTime->getTimestamp() - $timestamp; //diff in seconds.

        $months = 0;
        $days = 0;
        $hours = 0;
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
         * (30 days)
         */
        if ($days >= 30) {
            $months = floor($days / 30);
        }

        return array(
            'seconds'   => $seconds,
            'minutes'   => $minutes,
            'hours'     => $hours,
            'days'      => $days,
            'months'    => $months
        );
    }

    /**
     * Converts seconds into human readable format.
     */
    public function secondsToHumanReadable(int $seconds): string {
        $lang = $this->text->getCurrentLanguage();

        if ($lang == 'ru_RU') {
            return $this->secondsToHumanReadableRussian($seconds);
        } elseif ($lang == 'en_US') {
            return $this->secondsToHumanReadableEnglish($seconds);
        } else {
            return $this->secondsToHumanReadableEnglish($seconds);
        }
    }

    private function secondsToHumanReadableRussian(int $seconds): string {
        $days = intval($seconds / 86400);
        $remainingHours = intval(($seconds % 86400) / 3600);
        $remainingMinutes = intval(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        $dayWord = $this->declension($days, array('день', 'дня', 'дней'));
        $hourWord = $this->declension($remainingHours, array('час', 'часа', 'часов'));
        $minuteWord = $this->declension($remainingMinutes, array('минута', 'минуты', 'минут'));
        $secondWord = $this->declension($remainingSeconds, array('секунда', 'секунды', 'секунд'));


        if ($days > 1) {
            if ($days > 3) return "{$days} {$dayWord}";
            else return "{$days} {$dayWord} {$remainingHours} {$hourWord}";
        }
        if ($remainingHours > 1) {
            if ($remainingHours > 3) return "{$remainingHours} {$hourWord}";
            else return "{$remainingHours} {$hourWord} {$remainingMinutes} {$minuteWord}";
        }
        if ($remainingMinutes > 1) {
            if ($remainingMinutes > 15) return "{$remainingMinutes} {$minuteWord}";
            else return "{$remainingMinutes} {$minuteWord} {$remainingSeconds} {$secondWord}";
        }
        
        return $remainingSeconds > 1 ? "{$remainingSeconds} {$secondWord}" : "только что";
    }
    

    private function secondsToHumanReadableEnglish(int $seconds): string {
        $days = intval($seconds / 86400);
        $remainingHours = intval(($seconds % 86400) / 3600);
        $remainingMinutes = intval(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        $dayWord = $this->declension($days, array('day', 'days', 'days'));
        $hourWord = $this->declension($remainingHours, array('hour', 'hours', 'hours'));
        $minuteWord = $this->declension($remainingMinutes, array('minute', 'minutes', 'minutes'));
        $secondWord = $this->declension($remainingSeconds, array('second', 'seconds', 'seconds'));


        if ($days > 1) {
            if ($days > 3) return "{$days} {$dayWord}";
            else return "{$days} {$dayWord} {$remainingHours} {$hourWord}";
        }
        if ($remainingHours > 1) {
            if ($remainingHours > 3) return "{$remainingHours} {$hourWord}";
            else return "{$remainingHours} {$hourWord} {$remainingMinutes} {$minuteWord}";
        }
        if ($remainingMinutes > 1) {
            if ($remainingMinutes > 15) return "{$remainingMinutes} {$minuteWord}";
            else return "{$remainingMinutes} {$minuteWord} {$remainingSeconds} {$secondWord}";
        }
        
        return $remainingSeconds > 1 ? "{$remainingSeconds} {$secondWord}" : "только что";
    }

    /**
     * hours, minutes
     */
    private function getRelativeTimeRussian(int $timestamp): string {
        if ($timestamp == 0) return 'никогда';
        $now  = time();
        $diff = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $hours   = $diff->h;
        $minutes = $diff->i;
    
        $result = '';
    
        if ($hours > 0) {
            $result .= $hours . ' ' . $this->declension($hours, array('час', 'часа', 'часов')) . ' ';
        }
    
        if ($minutes > 0) {
            $result .= $minutes . ' ' . $this->declension($minutes, array('минута', 'минуты', 'минут')) . ' ';
        }
    
        /**
         * Check if the timestamp is in the future or in the past.
         */
        if ($timestamp > $now) {
            $result = 'через ' . $result;
        } else {
            $result = $result . 'назад';
        }

        if ($result == 'назад') return 'только что';

        if ($result == '1 минута назад') return 'минуту назад';
    
        return $result;
    }

    /**
     * hours, minutes
     */
    private function getRelativeTimeEnglish(int $timestamp): string {
        if ($timestamp == 0) return 'never';
        $now  = time();
        $diff = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
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
        
        if ($result == 'ago') return 'just now';

        return $result;
    }
    
    

    private function getShortRelativeTimeRussian(int $timestamp): string {
        $now    = time();
        $diff   = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $years  = $diff->y;
        $months = $diff->m;
        $days   = $diff->d;
    
        $result = '';

        if ($years > 0) $result .= $years . ' ' . $this->declension($years, array('год', 'года', 'лет')) . ' ';
        if ($months > 0) $result .= $months . ' ' . $this->declension($months, array('месяц', 'месяца', 'месяцев')) . ' ';
        if ($days > 0) $result .= $days . ' ' . $this->declension($days, array('день', 'дня', 'дней')) . ' ';

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

    private function getShortRelativeTimeEnglish(int $timestamp): string {
        $now    = time();
        $diff   = date_diff(new DateTime('@' . $timestamp), new DateTime('@' . $now));
        $years  = $diff->y;
        $months = $diff->m;
        $days   = $diff->d;

        $result = '';

        if ($years > 0) $result .= $years . ' year' . ($years > 1 ? 's ' : ' ');
        if ($months > 0) $result .= $months . ' month' . ($months > 1 ? 's ' : ' ');
        if ($days > 0) $result .= $days . ' day' . ($days > 1 ? 's ' : ' ');

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


    private function declension($number, $words) {
        $number = abs($number);
        if ($number > 20) $number %= 10;
        if ($number == 1) return $words[0];
        if ($number >= 2 && $number <= 4) return $words[1];
        return $words[2];
    }
}