<?php

namespace PackBot\Tests\Functional;

use PackBot\IncidentStatistics;
use PackBot\Site;
use PackBot\SiteMonitoringDB;

/**
 * Base class for function command tests.
 */
class IncidentStatisticsTest extends TestWithEnvCase
{
    public function testIncidentsCount()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/' . mt_rand(1, 1000);

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];
        $site   = new Site($siteID);

        TestHelpers::insertExampleIncidents($siteID);

        $statistics = new IncidentStatistics($site);

        $this->assertEquals(3, $statistics->getIncidentsCount());
        $this->assertEquals(1, $statistics->getWrongCodeIncidentsCount());
        $this->assertEquals(2, $statistics->getTimeoutIncidentsCount());
    }

    public function testIncidentsAverageTime()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/' . mt_rand(1, 1000);

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];
        $site   = new Site($siteID);

        TestHelpers::insertExampleIncidents($siteID);

        $statistics = new IncidentStatistics($site);

        $this->assertEquals(61223, $statistics->getAverageIncidentsTime());
    }

    public function testLastIncidentTime()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/' . mt_rand(1, 1000);

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];
        $site   = new Site($siteID);

        TestHelpers::insertExampleIncidents($siteID);

        $statistics = new IncidentStatistics($site);

        $this->assertEquals(183600, $statistics->getLastIncidentTime());
    }

    public function testTimeSinceLastIncident()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/' . mt_rand(1, 1000);

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];
        $site   = new Site($siteID);

        TestHelpers::insertExampleIncidents($siteID);

        $statistics = new IncidentStatistics($site);

        $this->assertEquals(0, $statistics->getTimeSinceLastIncident());
    }

    public function testLongestIncidentTime()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/' . mt_rand(1, 1000);

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];
        $site   = new Site($siteID);

        TestHelpers::insertExampleIncidents($siteID);

        $statistics = new IncidentStatistics($site);

        $this->assertEquals(183600, $statistics->getLongestIncidentTime());
    }
}
