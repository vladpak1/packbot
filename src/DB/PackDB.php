<?php
namespace PackBot;

use PDO;

/**
 * This class is represents the pack database.
 */
class PackDB {

    protected static PDO $pdo;

    public static function connect() {
        $host     = Environment::var('db_host');
        $user     = Environment::var('db_user');
        $password = Environment::var('db_password');
        $db       = Environment::var('db_name');
        $charset  = 'utf8';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$pdo = new PDO($dsn, $user, $password, $opt);
        } catch (\PDOException $e) {
            throw new EnvironmentException('Database connection failed: ' . $e->getMessage());
        }

    }

    /**
     * Get the last message ID for chat_id from db.
     */
    public static function getLastMessageIDByChatID(int $chat_id): int {
        $stmt = self::$pdo->prepare('SELECT `last_message_id` FROM `last_messages_id` WHERE `chat_id` = :chat_id');
        $stmt->execute(['chat_id' => $chat_id]);
        $result = $stmt->fetch();
        return $result['last_message_id'] ?? 0;
    }

    /**
     * Insert the last message ID for chat_id into db.
     */
    public static function memberLastMessageIDForChat(int $chat_id, int $message_id): void {
        $stmt = self::$pdo->prepare('INSERT INTO `last_messages_id` (`chat_id`, `last_message_id`) VALUES (:chat_id, :message_id) ON DUPLICATE KEY UPDATE `last_message_id` = VALUES(`last_message_id`)');
        $stmt->execute(['chat_id' => $chat_id, 'message_id' => $message_id]);
    }

    protected static function getDB() {
        if (!isset(self::$pdo)) {
            self::connect();
        }
        return self::$pdo;
    }

    /**
     * Get the chatID by userID in "message" table.
     * SELECT chat_id FROM `message` WHERE `user_id`
     */
    public static function getChatIDByUserID(int $user_id): int {
        $stmt = self::$pdo->prepare('SELECT `chat_id` FROM `message` WHERE `user_id` = :user_id');
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch();
        return $result['chat_id'] ?? 0;
    }

    /**
     * Get total messages count for today.
     * Using the "message" table and the "date" (mysql date) column.
     */
    public static function getTotalMessagesToday(): int {
        $today = date('Y-m-d');
        $stmt = self::$pdo->prepare('SELECT COUNT(*) as total_messages FROM `message` WHERE DATE(`date`) = :today');
        $stmt->execute(['today' => $today]);
        $result = $stmt->fetch();
        return $result['total_messages'] ?? 0;
    }

}