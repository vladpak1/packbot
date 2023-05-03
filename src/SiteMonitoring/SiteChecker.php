<?php

namespace PackBot;
use Throwable;

class SiteChecker {

    protected array $sites;

    protected array $settings;

    protected array $alerts = array();

    /**
     * This class is responsible for checking the sites.
     */
    public function __construct() {
        $this->getSites();
        $this->getSettings();
    }

    /**
     * Check all sites
     */
    public function checkSites() {

        /**
         * Check if check sites is temporarily disabled.
         */
        if ($this->settings['disabled']) {
            echo "Site checking is disabled." . PHP_EOL;
            return;
        }

        foreach ($this->sites as $site) {
            $this->checkSite($site);
        }
    }

    /**
     * Get alerts.
     * @return array Array with alerts.
     */
    public function getAlerts(): array {
        return $this->alerts;
    }

    protected function getSettings() {
        $this->settings = Environment::var('monitoring_settings')['siteChecker'];
    }

    protected function getSites() {
        $sites = SiteMonitoringDB::getSitesIDs();

        $objects = array();
        foreach ($sites as $id) {
            $objects[] = new Site($id);
        }

        $this->sites = $objects;
    }

    protected function checkSite(Site $site, $recheck = true, $forceDisableNoCache = false) {
        $url = $site->getURL();

        /**
         * If "noCacheUrl" is set to true, we will add a random string to the end of the URL.
         * This will prevent the site from being cached.
         */
        if ($this->settings['noCacheUrl'] && !$forceDisableNoCache) $url .= '?_' . uniqid();

        $curl = new Curl($url);
        echo "Checking site $url" . PHP_EOL;

        /**
         * Let's determine if we need to check the site.
         */
         if (!$this->isCheckRequired($site) && $recheck) return;

        /**
         * We slightly increase the true curl time limit to fix not the wrong code (0),
         * but the site response time.
         * If recheck is true, there is no need to increase the timeout.
         */
        $timeoutMagnifier = $recheck ? 0 : 15;
    
        try {
            $timeout      = $this->settings['downStateConditions']['timeout'];
            $response     = $curl->setTimeout($timeout + $timeoutMagnifier)->setFollowLocation(false)->execute()->getResponse();
            $responseCode = $response->getCode();
            $responseTime = $response->getTotalTime() / 1000;


            /**
             * Due to various network side effects,
             * we need to implement additional validation if
             * the site has been declared down.
             */
            if ($recheck && ($responseCode !== 200 || $responseTime > $timeout)) {
                echo "Site is declared down. Rechecking after 5 seconds..." . PHP_EOL;
                sleep(5);

                /**
                 * Some sites don't support URL parameters,
                 * so we'll disable them if we get code that looks like this.
                 */
                if ($responseCode == 301 || $responseCode == 302 || $responseCode == 400) {
                    echo "It seems that the site does not support URL parameters." . PHP_EOL;
                    $this->checkSite($site, false, true);
                    return;
                }


                $this->checkSite($site, false);
                return;
            }
    
            if ($responseCode !== 200 || $responseTime > $timeout) {
                echo "Site is still down after rechecking." . PHP_EOL;
                echo "Set down state for site: $url" . PHP_EOL;
                echo "Response code: $responseCode" . PHP_EOL;
                echo "Response time: $responseTime" . PHP_EOL;
    
                $reason = $responseCode !== 200 ? ['type' => 'wrongCode', 'code' => $responseCode] : ['type' => 'timeout', 'timeout' => round($responseTime)];
                $alertMethod = $site->getRawState() === 1 || $site->getRawState() === null ? 'createFirstAlert' : 'createAnotherAlert';
                $this->$alertMethod($site, $reason);
                $site->setDownState();
            } else {
                echo "Set up state for site: $url" . PHP_EOL;
    
                if ($site->getRawState() === 0) {
                    $this->createWorkingAgainAlert($site);
                }
    
                $site->setUpState();
            }
        } catch (Throwable $e) {
            error_log("Error occurred while checking site: $url Error: " . $e->getMessage());
        }
    }

    protected function createFirstAlert(Site $site, $reason) {
        $this->alerts[] = new Alert(array(
            'site_id' => $site->getID(),
            'alert_type'   => 'firstAlert',
            'reason' => $reason,
        ));
    }

    protected function createAnotherAlert(Site $site, $reason) {
        $this->alerts[] = new Alert(array(
            'site_id' => $site->getID(),
            'alert_type'   => 'anotherAlert',
            'reason' => $reason,
        ));
    }

    protected function createWorkingAgainAlert(Site $site) {
        $this->alerts[] = new Alert(array(
            'site_id' => $site->getID(),
            'alert_type'   => 'workingAgain',
            'reason' => false,
        ));
    }

    /**
     * Specifies whether the site should be checked now,
     * given the specified intervals and its state.
     * 
     * @param Site $site Site object.
     * @return bool True if site should be checked now, false otherwise.
     */
    protected function isCheckRequired(Site $site): bool {
        $lastCheck       = $site->getLastCheck();
        $time            = new Time();
        $passedMins      = $time->getTimestampsDifference($lastCheck)['minutes'];
        $regularInterval = $this->settings['siteCheckInterval'];
        $downInterval    = $this->settings['downSiteCheckInterval'];
        $siteState       = $site->getRawState();

        /**
         * If site is down, we need to apply downSiteCheckInterval for it.
         */
         if ($siteState === 0 && $passedMins >= $downInterval) {
            echo "Site is down and need to be checked more often." . PHP_EOL;
            return true;
         }

        /**
         * If site is up, we need to apply regular interval for it.
         */
        if ($siteState === 1 && $passedMins >= $regularInterval) {
            echo "Site is up and need to be checked." . PHP_EOL;
            return true;
        }

        /**
         * If site has never been checked, we need to check it.
         */
        if ($lastCheck === false || $lastCheck === null || $siteState === null) {
            echo "Site has never been checked and need to be checked." . PHP_EOL;
            return true;
        }

        echo "Site doesn't need to be checked." . PHP_EOL;
        return false;
    }
}
