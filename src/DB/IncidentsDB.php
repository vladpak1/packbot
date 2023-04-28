<?php

namespace PackBot;

use PDO;

/**
 * This class is representing a database connection for Incidents.
 * 
 * Database scheme:
 * 1. "id" int primary: Unique internal ID of the incident.
 * 2. "start_time" timestamp default current_timestamp: Timestamp of the incident start.
 * 3. "end_time" timestamp: Timestamp of the incident end.
 * 4. "site_id" int: ID of the site.
 * 5. "data" text: Serialized array of data for incident.
 */
class IncidentsDB extends PackDB {

    /**
     * Inserts a new incident into the database.
     * 
     * @param int $siteID ID of the site.
     * @param array $data Data for the incident.
     * @return int ID of the incident.
     */
    public static function insertIncident(int $siteID, array $data): int {
        $sql = "INSERT INTO incidents (site_id, data) VALUES (:site_id, :data)";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID, 'data' => serialize($data)]);
        return self::getLastIncidentID();
    }

    /**
     * Sets the end time of the incident.
     */
    public static function resolveIncident(int $id) {
        $sql = "UPDATE incidents SET end_time = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Get incident by ID.
     * 
     * @param int $id ID of the incident.
     * @return array Incident data.
     */
    public static function getIncident(int $id): array {
        $sql = "SELECT * FROM incidents WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getIncidentsCount(int $siteID): int {
        $sql = "SELECT COUNT(*) FROM incidents WHERE site_id = :site_id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns the sum of the time in seconds for all incidents on the site.
     */
    public static function getIncidentsTime(int $siteID): int {
        $sql = "SELECT SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) FROM incidents WHERE site_id = :site_id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);

        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        if ($result === null) return 0;

        return $result;
    }

    /**
     * Returns the total number of incidents with type "wrongCode".
     */
    public static function getWrongCodeIncidentsCount(int $siteID): int {
        $sql = "SELECT COUNT(*) FROM incidents WHERE site_id = :site_id AND data LIKE '%wrongCode%'";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Return the total number of incidents with type "timeout".
     */
    public static function getTimeoutIncidentsCount(int $siteID): int {
        $sql = "SELECT COUNT(*) FROM incidents WHERE site_id = :site_id AND data LIKE '%timeout%'";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns the duration of the longest incident on the site in seconds.
     */
    public static function getLongestIncidentDuration(int $siteID): int {
        $sql = "SELECT MAX(TIMESTAMPDIFF(SECOND, start_time, end_time)) FROM incidents WHERE site_id = :site_id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        if ($result === null) return 0;
        return $result;
    }

    /**
     * Returns the last incident for site.
     */
    public static function getLastIncident(int $siteID): array {
        $sql = "SELECT * FROM incidents WHERE site_id = :site_id ORDER BY id DESC LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Returns the last incident duration in seconds.
     */
    public static function getLastIncidentTime(int $siteID): int {
        $sql = "SELECT TIMESTAMPDIFF(SECOND, start_time, end_time) FROM incidents WHERE site_id = :site_id ORDER BY id DESC LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public static function isThereActiveIncidentsForSite(int $siteID): bool {
        $sql = "SELECT COUNT(*) FROM incidents WHERE site_id = :site_id AND end_time IS NULL";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_COLUMN) > 0;
    }

    /**
     * Returns time in second since the end of the last incident.
     */
    public static function getTimeSinceLastIncident(int $siteID): int {
        $sql = "SELECT TIMESTAMPDIFF(SECOND, end_time, CURRENT_TIMESTAMP) FROM incidents WHERE site_id = :site_id ORDER BY id DESC LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns all incidents for the site,
     * sorted by start date (most recent first).
     */
    public static function getIncidents(int $siteID): array {
        $sql = "SELECT * FROM incidents WHERE site_id = :site_id ORDER BY start_time DESC";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns all incidents IDs for the site,
     * sorted by start date (most recent first).
     */
    public static function getIncidentsIDs(int $siteID): array {
        $sql = "SELECT id FROM incidents WHERE site_id = :site_id ORDER BY start_time DESC";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['site_id' => $siteID]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected static function getLastIncidentID(): int {
        $sql = "SELECT id FROM incidents ORDER BY id DESC LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
}