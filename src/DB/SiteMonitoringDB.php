<?php

namespace PackBot;

use PDO;

/**
 * This class is representing a database connection for Site Monitoring.
 * 
 * Database scheme:
 * 1. "id" int primary: Unique internal ID of the site.
 * 2. "url" varchar(255): URL of the site.
 * 3. "last_check" bigint: Timestamp of the last check.
 * 4. "owners" text: Comma-separated list of owners' IDs.
 * 5. "data" text: Serialized array of data for site.
 */
class SiteMonitoringDB extends PackDB {

    /**
     * Get all sites of the user.
     * @param int $userID ID of the user.
     * @return array Array of sites.
     */
    public static function getUsersSites(int $userID): array {
        $sql = "SELECT * FROM site_monitoring WHERE owners REGEXP :userID";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['userID' => "(^|,)($userID)(,|$)"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSitesIDs() {
        $sql = "SELECT id FROM site_monitoring";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getSite(int $id): array {
        try {
            $sql = "SELECT * FROM site_monitoring WHERE id = :id";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new SiteMonitoringException($e->getMessage());
        }
    }

    /**
     * Set site state to down and updates last_check timestamp.
     */
    public static function setSiteDownState(int $id) {
        $sql = "UPDATE site_monitoring SET state = 0, last_check = :last_check WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $id, 'last_check' => time()]);
    }

    /**
     * Set site status as working (it may already be set as working) and updates last_check timestamp.
     */
    public static function setSiteWorkingState(int $id) {
        $sql = "UPDATE site_monitoring SET state = 1, last_check = :last_check WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $id, 'last_check' => time()]);
    }

    public static function getUsersSitesCount(int $userID): int {
        $sql = "SELECT COUNT(*) FROM site_monitoring WHERE owners REGEXP :userID";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['userID' => "(^|,)($userID)(,|$)"]);
        return $stmt->fetchColumn();
    }

    /**
     * Get all users assigned to the site.
     */
    public static function getSiteOwners(int $id): array {
        $sql = "SELECT owners FROM site_monitoring WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return explode(',', $result['owners']) == array('') ? array() : array_filter(explode(',', $result['owners']), 'strlen');
        } else {
            return array();
        }
    }

    public static function getUsersSitesIDs(int $userID): array {
        $sql = "SELECT id FROM site_monitoring WHERE owners REGEXP :userID";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['userID' => "(^|,)($userID)(,|$)"]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function isUserHasSite(string $url, int $userID): bool {
        $sql = "SELECT * FROM site_monitoring WHERE url = :url";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['url' => $url]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $owners = explode(',', $result['owners']);
            if (in_array($userID, $owners)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Creates site in the database with no owners.
     * Do not create site if it already exists.
     */
    public static function addSite(string $url): bool {
        try {
                // Check if the site already exists in the database
            $sql = "SELECT * FROM site_monitoring WHERE url = :url";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute(['url' => $url]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                // If the site doesn't exist, insert a new row with the provided URL and empty owners
                $sql = "INSERT INTO site_monitoring (url, last_check, owners, data) VALUES (:url, 0, '', '')";
                $stmt = self::getDB()->prepare($sql);
                $success = $stmt->execute(['url' => $url]);

                // Return true if the site was added successfully, false otherwise
                return $success;
            } else {
                // If the site already exists, return false
                return false;
            }
        }  catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function clearSiteData(int $siteID) {
        $sql = "UPDATE site_monitoring SET data = '' WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $siteID]);
    }

    public static function isSiteExists(string $url): bool {
        $sql = "SELECT * FROM site_monitoring WHERE url = :url";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['url' => $url]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function isSiteWithIDExists(int $id): bool {
        $sql = "SELECT * FROM site_monitoring WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function isSiteAssignedToUser(string $url, int $userID): bool {
        $sql = "SELECT * FROM site_monitoring WHERE url = :url";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['url' => $url]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $owners = explode(',', $result['owners']);
            if (in_array($userID, $owners)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function assignOwnerToSite(string $url, int $userID): bool {
        $sql = "SELECT * FROM site_monitoring WHERE url = :url";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['url' => $url]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $owners = explode(',', $result['owners']);
            if (!in_array($userID, $owners)) {
                $owners[] = $userID;
                $sql = "UPDATE site_monitoring SET owners = :owners WHERE url = :url";
                $stmt = self::getDB()->prepare($sql);
                $success = $stmt->execute(['owners' => implode(',', $owners), 'url' => $url]);
                return $success;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function unassignOwnerFromSite(int|string $site, int $userID): bool {
        if (is_numeric($site)) {
            $sql = "SELECT * FROM site_monitoring WHERE id = :id";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute(['id' => $site]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT * FROM site_monitoring WHERE url = :url";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute(['url' => $site]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if ($result) {
            $owners = explode(',', $result['owners']);
            if (in_array($userID, $owners)) {
                $owners = array_diff($owners, [$userID]);
                $sql = "UPDATE site_monitoring SET owners = :owners WHERE id = :id";
                $stmt = self::getDB()->prepare($sql);
                $success = $stmt->execute(['owners' => implode(',', $owners), 'id' => $result['id']]);
                return $success;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Merge new site data with existing data and put in serialized form.
     */
    public static function updateSiteData(array $data, int $siteID) {
        $sql = "SELECT data FROM site_monitoring WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $siteID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $oldData = $result['data'] == '' ? [] : unserialize($result['data']);
            $newData = array_merge($oldData, $data);
            $sql = "UPDATE site_monitoring SET data = :data WHERE id = :id";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute(['data' => serialize($newData), 'id' => $siteID]);
        }
    }

    /**
     * Returns the total number of site in monitoring.
     * For development purposes only.
     */
    public static function getTotalSites(): int {
        $sql = "SELECT COUNT(*) FROM site_monitoring";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns the total number of all sites with state 0 (not working)
     * For development purposes only.
     */
    public static function getTotalSitesWithProblems() {
        $sql = "SELECT COUNT(*) FROM site_monitoring WHERE state = 0";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public static function getAllSites() {
        $sql = "SELECT * FROM site_monitoring";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns the total number of users assigned to sites.
     */
    public static function getTotalUsersAssignedToSites() {
        $sites  = self::getAllSites();
        $owners = array();

        foreach ($sites as $site) {
            $owners[] = array_filter(explode(',', $site['owners']), 'strlen');
        }

        $owners = array_values(array_unique(array_merge(...$owners)));
        
        return array(
            'count'  => count($owners),
            'owners' => $owners
        );
    }

    /**
     * Returns the last checked site timestamp.
     */
    public static function getLastCheckedSiteTimestamp() {
        $sql = "SELECT MAX(last_check) FROM site_monitoring";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Removes sites from database that does not have any owners assigned.
     * Return the number of removed sites.
     */
    public static function removeSitesWithoutOwners(): int {
        $sql = "SELECT * FROM site_monitoring";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $removed = 0;
        foreach ($sites as $site) {
            if ($site['owners'] == '') {
                self::removeSite($site['id']);
                $removed++;
            }
        }
        return $removed;
    }
     
    /**
     * Removes the site from database and all incidents related to it.
     */
    public static function removeSite(int $siteID): bool {
        $sql = "DELETE FROM site_monitoring WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(['id' => $siteID]);

        //Remove all incidents related to this site
        IncidentsDB::removeIncidents($siteID);

        return true;
    }



}