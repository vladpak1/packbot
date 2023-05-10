<?php

namespace PackBot;

class IncidentStatistics
{
    protected Site $site;

    /**
     * This class is responsible for generating statistics about incidents.
     *
     * NOTE:
     * All incidents older than 30 days are removed from the database.
     * Therefore, retrieving incidents directly from the database assumes
     * that the sample contains only incidents in the last 30 days.
     *
     * @param  Site              $site Site to generate statistics for.
     * @throws IncidentException
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Returns number of incidents for the site.
     */
    public function getIncidentsCount(): int
    {
        return IncidentsDB::getIncidentsCount($this->site->getID());
    }

    /**
     * Returns site's uptime in percentage for the last 30 days.
     */
    public function getUptime(): float
    {
        $totalIncidentsTime = IncidentsDB::getIncidentsTime($this->site->getID());
        $secondsIn30Days    = 30 * 24 * 60 * 60;
        $uptimeSeconds      = $secondsIn30Days - $totalIncidentsTime;
        $uptimePercentage   = ($uptimeSeconds / $secondsIn30Days) * 100;

        return round($uptimePercentage, 2);
    }

    /**
     * Returns number of incidents with type "wrongCode".
     */
    public function getWrongCodeIncidentsCount(): int
    {
        return IncidentsDB::getWrongCodeIncidentsCount($this->site->getID());
    }

    /**
     * Returns number of incidents with type "timeout".
     */
    public function getTimeoutIncidentsCount(): int
    {
        return IncidentsDB::getTimeoutIncidentsCount($this->site->getID());
    }

    /**
     * Return average time of incidents in seconds.
     */
    public function getAverageIncidentsTime(): int
    {
        $totalIncidentsTime = IncidentsDB::getIncidentsTime($this->site->getID());

        if (0 === $totalIncidentsTime) {
            return 0;
        } // Avoid division by zero (if there are no incidents).
        $incidentsCount = $this->getIncidentsCount();
        $averageTime    = $totalIncidentsTime / $incidentsCount;

        return round($averageTime);
    }

    /**
     * Returns the duration of the longest incident on the site in seconds.
     */
    public function getLongestIncidentTime(): int
    {
        return IncidentsDB::getLongestIncidentDuration($this->site->getID());
    }

    /**
     * Returns the last incident time in seconds.
     */
    public function getLastIncidentTime(): int
    {
        return IncidentsDB::getLastIncidentTime($this->site->getID());
    }

    /**
     * Returns number of seconds passed since the last incident.
     */
    public function getTimeSinceLastIncident(): int|null
    {
        if (IncidentsDB::isThereActiveIncidentsForSite($this->site->getID())) {
            return 0;
        }

        return IncidentsDB::getTimeSinceLastIncident($this->site->getID());
    }

    /**
     * Returns all incidents for the site, sorted by start date (most recent first).
     */
    public function getIncidents(): array
    {
        return IncidentsDB::getIncidents($this->site->getID());
    }

    /**
     * Returns all incidents IDs for the site, sorted by start date (most recent first).
     */
    public function getIncidentsIDs(): array
    {
        return IncidentsDB::getIncidentsIDs($this->site->getID());
    }

    /**
     * Returns the total incidents duration for the site in seconds.
     */
    public function getIncidentsDuration(): int
    {
        return IncidentsDB::getIncidentsTime($this->site->getID());
    }
}
