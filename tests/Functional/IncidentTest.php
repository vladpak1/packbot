<?php

namespace PackBot\Tests\Functional;

use PackBot\Incident;
use PackBot\IncidentFactory;
use PackBot\SiteMonitoringDB;
use PackBot\UserSettings;

/**
 * Base class for function command tests.
 */
class IncidentTest extends TestWithEnvCase
{
    public function testAddingIncident()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/' . mt_rand(1, 1000);

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];

        /**
         * Adding incident to example site.
         */
        $incident = IncidentFactory::createIncident($siteID, [
            'type' => 'wrongCode',
            'code' => 404,
        ]);

        $this->assertLessThan(10, $incident->getDuration());
        $this->assertEquals($siteID, $incident->getSiteID());
        $this->assertEquals('wrongCode', $incident->getType());
        $this->assertIsInt($incident->getID());
    }

    public function testResolvingIncident()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/';

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];

        /**
         * Adding incident to example site.
         */
        $incident = IncidentFactory::createIncident($siteID, [
            'type'    => 'timeout',
            'timeout' => 10,
        ]);
        $incidentID = $incident->getID();

        $this->assertFalse($incident->isIncidentResolved());
        $incident->resolve();
        $this->assertTrue($incident->isIncidentResolved());
    }

    public function testDurationStrings()
    {
        /**
         * Adding example site for adding incident to it.
         */
        $userID  = mt_rand(1, 1000);
        $testUrl = 'https://example.com/';

        SiteMonitoringDB::addSite($testUrl);
        SiteMonitoringDB::assignOwnerToSite($testUrl, $userID);
        $siteID = SiteMonitoringDB::getUsersSitesIDs($userID)[0];

        TestHelpers::insertExampleIncidents($siteID);

        $userID = mt_rand(1, 1000);
        UserSettings::setUserID($userID);
        $settings = new UserSettings($userID);

        /**
         * Incident #1.
         */
        $incident = new Incident(1);

        $settings->set('language', 'ru_RU');
        $this->assertEquals('1 минута', $incident->getDurationString());

        $settings->set('language', 'en_US');
        $this->assertEquals('1 minute', $incident->getDurationString());

        /**
         * Incident #2.
         */
        $incident = new Incident(2);

        $settings->set('language', 'ru_RU');
        $this->assertEquals('10 секунд', $incident->getDurationString());

        $settings->set('language', 'en_US');
        $this->assertEquals('10 seconds', $incident->getDurationString());

        /**
         * Incident #3.
         */
        $incident = new Incident(3);

        $settings->set('language', 'ru_RU');
        $this->assertEquals('2 дня 3 часа', $incident->getDurationString());

        $settings->set('language', 'en_US');
        $this->assertEquals('2 days 3 hours', $incident->getDurationString());
    }

    public function testNonExistingIncident()
    {
        $this->expectException(\PackBot\IncidentException::class);
        $this->expectExceptionMessage('Incident with ID 0 does not exist.');

        $incident = new Incident(0);
    }
}
