<?php

namespace PackBot;

class IncidentFactory
{
    /**
     * Create a new incident and return it.
     *
     * @param int   $siteID ID of the site.
     * @param array $data   Data for the incident.
     * @var $data['type'] string Type of the incident.
     * @var $data['code'] int Response code that caused the incident.
     * @var $data['timeout'] int Timeout that caused the incident.
     * @throws IncidentException
     */
    public static function createIncident(int $siteID, array $data): Incident
    {
        /**
         * Check if the site exists.
         */
        if (!SiteMonitoringDB::isSiteWithIDExists($siteID)) {
            throw new IncidentException("Site with ID $siteID does not exist.");
        }

        $id = IncidentsDB::insertIncident($siteID, $data);

        try {
            return new Incident($id);
        } catch (IncidentException $e) {
            throw new IncidentException("Failed to create incident with ID $id.");
        }
    }
}
