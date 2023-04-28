<?php

namespace PackBot;

class AdminStatistics extends Entity {

    protected array $statistics;

    /**
     * This class is representing the statistics of the bot.
     * Used by the admin commands.
     */
    public function __construct() {
        $this->prepareStatistics();
    }

    public function getMonitoringStats(): array {
        return $this->statistics['siteMonitoring'];
    }

    public function getRawData(): object {
        return (object) $this->statistics;
    }

    public function __toString(): string {
        return json_encode($this->statistics);
    }

    public function jsonSerialize(): string {
        return json_encode($this->statistics);
    }


    protected function prepareStatistics() {

        $time = new Time();

        $this->statistics['global'] = array(
            'messagesToday' => PackDB::getTotalMessagesToday(),
        );

        $this->statistics['siteMonitoring'] = array(
            'totalSites'    => SiteMonitoringDB::getTotalSites(),
            'outageSites'   => SiteMonitoringDB::getTotalSitesWithProblems(),
            'siteOwners'    => SiteMonitoringDB::getTotalUsersAssignedToSites(),
            'lastCheck'     => $time->relative_time_string(SiteMonitoringDB::getLastCheckedSiteTimestamp()),
        ); 
    }
}
