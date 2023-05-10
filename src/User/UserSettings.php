<?php

namespace PackBot;

class UserSettings
{
    protected array $settings;

    public function __construct()
    {
        $this->getSettingsFromDB();
    }

    /**
     * Get user setting.
     * If there is no such setting, return default.
     */
    public function get(string $key)
    {
        if ($this->isSettingExists($key)) {
            return $this->settings[$key];
        }

        return $this->getDefault($key);

    }

    public function set(string $key, $value)
    {
        $this->settings[$key] = $value;
        $this->saveSettingsToDB();
    }

    public function setArray(array $settings)
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getDefault(string $key)
    {
        $defaults = $this->getDefaults();

        if (isset($defaults[$key])) {
            return $defaults[$key];
        }

        throw new \Exception('No default value for setting ' . $key);

    }

    public function getDefaults()
    {
        return [
            'language' => 'ru_RU',
        ];
    }

    public function isSettingExists(string $key): bool
    {
        return isset($this->settings[$key]);
    }

    public function getAll()
    {
        return $this->settings;
    }

    /**
     * Check if user has settings in DB.
     */
    public function isUserHasSettings(): bool
    {
        return !empty($this->settings);
    }

    public static function setUserID(int $userID)
    {
        UserSettingsDB::$userID = $userID;

        if (self::isUserBanned()) {
            //todo
            throw new \Longman\TelegramBot\Exception\TelegramException('User banned!');
        }
    }

    private function getSettingsFromDB()
    {
        $settings = UserSettingsDB::getSettings();

        if ($settings) {
            $this->settings = $settings;
        } else {
            $this->settings = [];
        }
    }

    private function saveSettingsToDB()
    {
        UserSettingsDB::saveSettings($this->settings);
    }

    private static function isUserBanned(): bool
    {
        return in_array(UserSettingsDB::$userID, Environment::var('banList'));
    }
}
