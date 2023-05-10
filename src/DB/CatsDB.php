<?php

namespace PackBot;

use PDO;

/**
 * This class is representing a database connection to the cats table.
 *
 * Database scheme:
 * 1. user_id int primary: The ID of the user that the data refers to.
 * 2. images_seen text: A JSON array of the images that the user has seen.
 */
class CatsDB extends PackDB
{
    /**
     * Adds an image to the images_seen array of the user.
     * If there's no entry for the user, it will be created.
     */
    public static function addToSeen(int $userID, int $imgID)
    {
        $query = self::getDB()->prepare('SELECT images_seen FROM cats WHERE user_id = :user_id');
        $query->execute(['user_id' => $userID]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $query = self::getDB()->prepare('INSERT INTO cats (user_id, images_seen) VALUES (:user_id, :images_seen)');
            $query->execute(['user_id' => $userID, 'images_seen' => json_encode([$imgID])]);
        } else {
            $imagesSeen = json_decode($result['images_seen']);
            array_push($imagesSeen, $imgID);
            $query = self::getDB()->prepare('UPDATE cats SET images_seen = :images_seen WHERE user_id = :user_id');
            $query->execute(['user_id' => $userID, 'images_seen' => json_encode($imagesSeen)]);
        }
    }

    /**
     * Returns an array of all images that the user has seen.
     * If there's no entry for the user, it will return an empty array.
     */
    public static function getSeenImgs(int $userID): array
    {
        $query = self::getDB()->prepare('SELECT images_seen FROM cats WHERE user_id = :user_id');
        $query->execute(['user_id' => $userID]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return [];
        }

        return json_decode($result['images_seen']);
    }

    /**
     * Clear the images_seen array of the user.
     */
    public static function clearSeen(int $userID)
    {
        $query = self::getDB()->prepare('UPDATE cats SET images_seen = :images_seen WHERE user_id = :user_id');
        $query->execute(['user_id' => $userID, 'images_seen' => json_encode([])]);
    }
}
