<?php

namespace PackBot;

use PDO;

/**
 * This class is representing a database connection for user settings.
 */
class UserSettingsDB extends PackDB {


    public static int $userID = 0;

    public static function getSettings(): array {
        $userID = self::$userID;
        $sql = "SELECT * FROM user_settings WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $userID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            if ($result == '') {
                return array();
            } else {
                return json_decode($result['settings'], true);
            }
        } else {
            return array();
        }
    }

    public static function saveSettings(array $settings): void {
        $userID = self::$userID;
        $sql = "INSERT INTO user_settings (id, settings) VALUES (:id, :settings) ON DUPLICATE KEY UPDATE settings = VALUES(settings)";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $userID, 'settings' => json_encode($settings)]);
    }


}